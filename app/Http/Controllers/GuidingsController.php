<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGuidingRequest;
use App\Http\Requests\StoreNewGuidingRequest;
use App\Models\Gallery;
use App\Models\Guiding;
use App\Models\Target;
use App\Models\Method;
use App\Models\Water;
use App\Models\GuidingRequest;
use App\Models\Inclussion;
use App\Models\GuidingBoatType;
use App\Models\GuidingBoatDescription;
use App\Models\GuidingAdditionalInformation;
use App\Models\GuidingRequirements;
use App\Models\GuidingRecommendations;
use App\Traits\GuidingFilterOptimization;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Auth;
use Config;
use Illuminate\Support\Facades\Mail;
use App\Mail\GuidingRequestMail;
use App\Mail\SearchRequestUserMail;
use Illuminate\Support\Facades\Log;
use App\Models\ExtrasPrice;
use App\Services\CalendarScheduleService;
use App\Models\BoatExtras;
use App\Models\CategoryEntity;
use App\Models\Review;
use Illuminate\Support\Facades\Cache;
use App\Services\GuidingFilterService;
use App\Services\ImageOptimizationService;
use App\Services\Translation\GuidingTranslationService;
use App\Services\Media\ListingGalleryRetention;
use App\Services\Media\ListingMediaRelocator;
use App\Services\Media\MediaTrashService;
use App\Domain\Offers\OfferListingFilter;
use App\Services\Offers\OfferCatalogPageService;

class GuidingsController extends Controller
{
    use GuidingFilterOptimization;
    
    protected GuidingTranslationService $guidingTranslationService;

    /**
     * Normalize a guiding image path for consistent comparison (strip URL, leading slashes).
     */
    private function normalizeGuidingImagePath($path): ?string
    {
        if (is_array($path)) {
            $path = $path['path'] ?? $path['value'] ?? $path['url'] ?? reset($path);
        }

        if (!is_string($path)) {
            return null;
        }

        $parsedPath = parse_url($path, PHP_URL_PATH);
        if ($parsedPath) {
            $path = $parsedPath;
        }

        return ltrim($path, '/');
    }

    /**
     * Compare two normalized image path lists (order-independent).
     */
    private function guidingImageListsMatch(array $normalizedA, array $normalizedB): bool
    {
        $a = array_values(array_filter($normalizedA));
        $b = array_values(array_filter($normalizedB));
        sort($a);
        sort($b);

        return $a === $b;
    }

    private function resolveGuidingOwnerUserId(StoreNewGuidingRequest $request): ?int
    {
        // Regular guides can only own as themselves (ignore forged user_id).
        if (!$this->isAdminActor($request)) {
            return auth('web')->id();
        }

        // Employees/admins may assign any guide as owner.
        if ($request->filled('user_id')) {
            return (int) $request->input('user_id');
        }

        // Prefer existing guiding owner on admin update/draft when user_id omitted.
        if ($request->filled('guiding_id')) {
            $existing = Guiding::find($request->input('guiding_id'));
            if ($existing) {
                return (int) $existing->user_id;
            }
        }

        // Admin create without an explicit owner (legacy form has no guide picker).
        return null;
    }

    private function assertCanModifyGuiding(Guiding $guiding, ?StoreNewGuidingRequest $request = null): void
    {
        // Admins/employees can edit any guiding.
        if ($this->isAdminActor($request)) {
            return;
        }

        if ((int) auth('web')->id() !== (int) $guiding->user_id) {
            abort(403, 'You are not allowed to modify this guiding.');
        }
    }

    /**
     * True when the current actor is an employee/admin.
     * Authorization must never rely on form fields like target_redirect.
     */
    private function isAdminActor(?StoreNewGuidingRequest $request = null): bool
    {
        return auth('employees')->check();
    }

    private function canActorPublishGuidings(StoreNewGuidingRequest $request): bool
    {
        if ($this->isAdminActor($request)) {
            return true;
        }

        return (bool) auth('web')->user()?->canPublishGuidings();
    }

    /**
     * Skip image processing when no new uploads and the client image_list matches the stored gallery.
     */
    private function shouldSkipGuidingImageProcessing(Guiding $guiding, StoreNewGuidingRequest $request, array $imageListNormalized): bool
    {
        if ($request->has('title_image')) {
            return false;
        }

        $currentGallery = json_decode($guiding->gallery_images ?? '[]', true) ?? [];
        $currentNormalized = array_values(array_filter(array_map(
            fn ($path) => $this->normalizeGuidingImagePath($path),
            $currentGallery
        )));

        if (empty($currentNormalized) && empty($imageListNormalized)) {
            return true;
        }

        return $this->guidingImageListsMatch($currentNormalized, $imageListNormalized);
    }

    /**
     * Move removed guiding images to the media trash after a successful DB commit.
     * Files still referenced by the saved gallery are never removed.
     */
    private function deleteGuidingImagePaths(array $paths, Guiding $guiding): void
    {
        $committed = json_decode($guiding->gallery_images ?? '[]', true) ?? [];
        if (is_string($guiding->thumbnail_path) && $guiding->thumbnail_path !== '') {
            $committed[] = $guiding->thumbnail_path;
        }

        $this->mediaTrash->trashMany($paths, $committed);
    }
    
    /**
     * Guiding Status Values:
     * 0 = Disabled (manually disabled by user via profile page)
     * 1 = Active/Published (completed and live)
     * 2 = Draft (not completed or work in progress)
     * 
     * Status Logic:
     * - New guidings start as draft (2)
     * - When completed, they become published (1)
     * - Status 0 or 1 guidings NEVER become draft (2) when edited
     * - Only guidings that were never published can be in draft status
     * - Manual disable/enable toggles between 0 and 1
     */

    public function __construct(
        GuidingTranslationService $guidingTranslationService,
        private ListingMediaRelocator $mediaRelocator,
        private ListingGalleryRetention $galleryRetention,
        private MediaTrashService $mediaTrash,
        private OfferCatalogPageService $offerCatalog,
    )
    {
        $this->initializeOptimizationServices();
        $this->guidingTranslationService = $guidingTranslationService;
    }

    /**
     * Global tours listing. Filters follow the same offers-style facets as /offers?type=tour,
     * via OfferCatalogPageService, so this page and the guidings destination pages share one
     * filter system instead of the legacy checkbox/AJAX pipeline.
     */
    public function index(Request $request)
    {
        $vm = $this->offerCatalog->buildForTours($request);

        return view('pages.guidings.index', [
            'vm' => $vm,
        ]);
    }

    public function otherGuidings(){
        return $this->getOtherGuidings();
    }

    public function otherGuidingsBasedByLocation($latitude, $longitude, $allGuidings)
    {
        return $this->getOtherGuidingsBasedByLocation($latitude, $longitude, $allGuidings);
    }

    /**
     * Generate guiding cards for a collection of guidings
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateCards(Request $request)
    {
        try {
            $guidingIds = $request->input('guiding_ids', []);

            if (empty($guidingIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No guiding IDs provided'
                ], 400);
            }
            
            // Get guidings by IDs - first check if they exist at all
            $allGuidings = Guiding::whereIn('id', $guidingIds)->get();

            // Get guidings by IDs - try different status values
            // First try status = 1 (published)
            $guidings = Guiding::whereIn('id', $guidingIds)
                ->publiclyVisible()
                ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                ->get();
            
            // If no published guidings found, try status = 'active' (string)
            if ($guidings->isEmpty()) {
                $guidings = Guiding::whereIn('id', $guidingIds)
                    ->where('status', 'active')
                    ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                    ->get();
            }
            
            // If still no guidings found, try status = 0 (draft/pending)
            if ($guidings->isEmpty()) {
                $guidings = Guiding::whereIn('id', $guidingIds)
                    ->where('status', 0)
                    ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                    ->get();
            }
            
            // If still no guidings found, try any status (for testing)
            if ($guidings->isEmpty()) {
                $guidings = Guiding::whereIn('id', $guidingIds)
                    ->with(['user', 'guidingTargets', 'guidingMethods', 'guidingWaters'])
                    ->get();
            }

            // Generate card HTML using a compact version for camp form
            // Apply translations so the cards reflect manual translations for the active locale
            $this->applyGuidingTranslations($guidings);

            $cardsHtml = view('components.guiding-card-compact', [
                'guidings' => $guidings
            ])->render();
            
            return response()->json([
                'success' => true,
                'html' => $cardsHtml,
                'count' => $guidings->count(),
                'debug' => [
                    'requested_ids' => $guidingIds,
                    'found_any_status' => $allGuidings->pluck('id')->toArray(),
                    'found_active' => $guidings->pluck('id')->toArray()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating guiding cards: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error generating guiding cards',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function newShow(string $slug, Request $request)
    {
        $locale = Config::get('app.locale');
        
        $query = Guiding::where('slug', $slug);
        
        $destination = null;

        // If coming from destination page, get the destination context (new structure)
        if ($request->has('from_destination') && $request->has('destination_id') && $request->has('destination_type')) {
            $destinationType = $request->input('destination_type');
            $destinationId = $request->input('destination_id');
            
            if ($destinationType === 'country') {
                $destination = CategoryEntity::countries()->find($destinationId);
            } elseif ($destinationType === 'region') {
                $destination = CategoryEntity::regions()->find($destinationId);
            } elseif ($destinationType === 'city') {
                $destination = CategoryEntity::cities()->find($destinationId);
            }
        }

        $user = Auth::user();
        if (Auth::guard('employees')->check()) {
            // Admins preview every tour, including drafts and deactivated ones.
        } elseif (! $user) {
            $query = $query->publiclyVisible();
        } else {
            $query = $query->where(function ($inner) use ($user) {
                $inner->publiclyVisible()
                    ->orWhere('user_id', $user->id);
            });
        }

        $guiding = $query->first();

        if (is_null($guiding)) {
            abort(404);
        }

        $guiding->healThumbnailPath();

        // $targetFish = $guiding->is_newguiding ? json_decode($guiding->target_fish, true) : $guiding->guidingTargets->pluck('id')->toArray();
        $targetFish = json_decode($guiding->target_fish, true);
        $fishingFrom = $guiding->fishing_from_id;
        $fishingType = $guiding->fishing_type_id;

        // Get reviews instead of ratings
        // $reviews = $guiding->reviews;
        $reviews = Review::where('guide_id', $guiding->user_id)
            ->with('booking', 'booking.registeredUser', 'booking.guestUser', 'booking.calendar_schedule', 'booking.blocked_event')
            ->get();
        $reviews_count = $reviews->count();

        // Calculate average scores
        $average_overall_score = $reviews_count > 0 ? $reviews->avg('overall_score') : 0;
        $average_guide_score = $reviews_count > 0 ? $reviews->avg('guide_score') : 0;
        $average_region_water_score = $reviews_count > 0 ? $reviews->avg('region_water_score') : 0;
        $average_grandtotal_score = $reviews_count > 0 ? $reviews->avg('grandtotal_score') : 0;

        $otherGuidings = Guiding::publiclyVisible()
            ->where('id', '!=', $guiding->id)
            ->where(function($query) use ($targetFish, $fishingFrom, $fishingType) {
                $query->where(function($q) use ($targetFish, $fishingFrom, $fishingType) {
                    if (!empty($targetFish)) {
                        $q->where(function($subQ) use ($targetFish) {
                            foreach ($targetFish as $fish) {
                                $subQ->orWhereJsonContains('target_fish', $fish);
                            }
                        });
                    }
                    
                    if (!empty($fishingFrom)) {
                        $q->orWhere(function($subQ) use ($fishingFrom) {
                            $subQ->where('fishing_from_id', $fishingFrom)
                                  ->orWhereHas('fishingFrom', function($subSubQ) use ($fishingFrom) {
                                      $subSubQ->where('id', $fishingFrom);
                                  });
                        });
                    }
                    
                    if (!empty($fishingType)) {
                        $q->orWhere(function($subQ) use ($fishingType) {
                            $subQ->where('fishing_type_id', $fishingType)
                                  ->orWhereHas('fishingTypes', function($subSubQ) use ($fishingType) {
                                      $subSubQ->where('id', $fishingType);
                                  });
                        });
                    }
                });
            })
            ->limit(4)
            ->get();

        $sameGuidings = Guiding::where('user_id', $guiding->user_id)
            ->where('id', '!=', $guiding->id)
            ->publiclyVisible()
            ->limit(10)
            ->get();

        // Translation logic
        $locale = app()->getLocale();
        if ($guiding->language !== $locale) {
            $translationService = new GuidingTranslationService();
            $translated = $translationService->getTranslatedGuiding($guiding, $locale);
            if ($translated) {
                $guiding->translated = $translated;
            }
        }
        $this->applyGuidingTranslations($sameGuidings);
        $this->applyGuidingTranslations($otherGuidings);

        $preselectedGuests = null;
        $productPageQuery = OfferListingFilter::productPageQueryFromInput($request->query());
        if ($request->filled('num_guests') || $request->filled('num_persons')) {
            $preselectedGuests = $guiding->resolveBookingGuestCount(
                OfferListingFilter::fromRequest($request->all())->numGuests
            );
        }

        return view('pages.guidings.newIndex', [
            'guiding' => $guiding,
            'same_guiding' => $sameGuidings,
            'reviews' => $reviews,
            'reviews_count' => $reviews_count,
            'average_overall_score' => $average_overall_score,
            'average_guide_score' => $average_guide_score,
            'average_region_water_score' => $average_region_water_score,
            'average_grandtotal_score' => $average_grandtotal_score,
            'other_guidings' => $otherGuidings,
            'destination' => $destination,
            'blocked_events' => $guiding->getBlockedEvents(),
            'preselectedGuests' => $preselectedGuests,
            'productPageQuery' => $productPageQuery,
        ]);
    }

    public function guidingsStore(StoreNewGuidingRequest $request)
    {
        try {
            DB::beginTransaction();

            $isDraft = $request->input('is_draft', 0) == 1;
            $isUpdate = $request->input('is_update', 0) == 1;
            $guiding = $isUpdate
                ? Guiding::findOrFail($request->input('guiding_id'))
                : new Guiding(['user_id' => $this->resolveGuidingOwnerUserId($request)]);

            if ($isUpdate) {
                $this->assertCanModifyGuiding($guiding, $request);
            }

            // Store original status for updates
            $originalStatus = $isUpdate ? $guiding->status : null;

            // Slug must exist before image uploads for new guidings
            if (!$isUpdate) {
                $guiding->slug = slugify(
                    ($request->input('title') ?? 'temp') . '-in-' . ($request->input('location') ?? 'location')
                );
            }

            $pathsToDelete = $this->fillGuidingFromRequest($guiding, $request, $isDraft);

            // Only enforce strict requirements if not draft
            // Count images based on the guiding's final gallery state
            $galleryImages = json_decode($guiding->gallery_images ?? '[]', true) ?? [];

            // If this is an update and no gallery images were explicitly provided in the request,
            // fall back to the original gallery so we don't miscount existing images.
            if ($isUpdate && empty($galleryImages)) {
                $originalGallery = json_decode($guiding->getOriginal('gallery_images') ?? '[]', true) ?? [];
                $galleryImages = $originalGallery;
            }

            // Filter out null/empty entries just in case
            $totalImageCount = count(array_filter($galleryImages));

            // Safety net: also check the image_list payload which represents the intended final order
            $imageListFromRequest = json_decode($request->input('image_list', '[]'), true);
            if (is_array($imageListFromRequest)) {
                $totalImageCount = max($totalImageCount, count(array_filter($imageListFromRequest)));
            }

            $isAdminActor = $this->isAdminActor($request);
            // Keep legacy redirect detection for post-save destination only (not authorization).
            $isAdminSubmit = $isAdminActor || str_contains($request->input('target_redirect', ''), 'admin');

            // Require 5 images only for non-draft, non-admin submissions (profile create/update)
            if (!$isDraft && !$isAdminActor && $totalImageCount < 5) {
                throw new \Exception('Please upload at least 5 images');
            }

            $guiding->is_newguiding = 1;

            $savedAsDraftDueToPending = false;

            // Smart status management
            if ($isDraft) {
                // Preserve status if it was ever published (1) or disabled (0)
                // Only set to draft (2) if it's new or was already a draft
                if ($isUpdate && ((int) $originalStatus === 1 || (int) $originalStatus === 0)) {
                    $guiding->status = $originalStatus; // Keep original status (0 or 1)
                } else {
                    $guiding->status = 2; // New guiding or was already draft
                }
            } elseif (! $this->canActorPublishGuidings($request)) {
                // Pending/rejected guides: save the tour but keep it unpublished (draft)
                if ($isUpdate && in_array((int) $originalStatus, [0, 1], true)) {
                    $guiding->status = $originalStatus;
                } else {
                    $guiding->status = 2;
                    $savedAsDraftDueToPending = true;
                }
            } else {
                $guiding->status = 1;
            }

            // Never imprint domain/locale onto guidings.language from create/edit POSTs
            // (admin and profile share this endpoint). Source language is only changed via
            // AdminGuidingsController::updateLanguage or artisan guiding:translate --detect-language.
            $this->rejectDomainDerivedSourceLanguage($guiding, $isUpdate, $isAdminActor);

            $guiding->save();
            $this->relocateGuidingMediaFromTemp($guiding);
            DB::commit();

            $this->deleteGuidingImagePaths($pathsToDelete, $guiding);

            try {
                $this->syncGuidingCalendarSchedule($guiding, $request);
            } catch (\Exception $calendarException) {
                Log::warning('Calendar schedule sync failed after guiding save', [
                    'guiding_id' => $guiding->id,
                    'error' => $calendarException->getMessage(),
                ]);
            }

            $redirectUrl = $request->input('target_redirect') ?? route('profile.myguidings');
            if ($savedAsDraftDueToPending) {
                $redirectUrl = route('profile.myguidings', ['notice' => 'pending_publish']);
            }

            $message = $isDraft
                ? __('profile.guiding_draft_saved')
                : __('profile.guiding_published_success');
            if ($savedAsDraftDueToPending) {
                $message = __('profile.guiding_saved_as_draft_pending_guide');
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_as_draft_due_to_pending' => $savedAsDraftDueToPending,
                'redirect_url' => $redirectUrl,
                'gallery_images' => json_decode($guiding->gallery_images ?? '[]', true) ?? [],
                'thumbnail_path' => $guiding->thumbnail_path,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in guidingsStore: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json(['error' => 'An error occurred while processing your request.' . $e->getMessage()], 500);
        }
    }

    public function saveDraft(StoreNewGuidingRequest $request)
    {
        try {
            $result = $this->persistGuidingDraft($request);

            return response()->json([
                'success' => true,
                'guiding_id' => $result['guiding_id'],
                'gallery_images' => $result['gallery_images'],
                'thumbnail_path' => $result['thumbnail_path'] ?? '',
                'message' => 'Draft saved successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error in saveDraft: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Persist a guiding draft synchronously with safe deferred image deletion.
     *
     * @return array{guiding_id: int, gallery_images: array, thumbnail_path: string|null}
     */
    private function persistGuidingDraft(StoreNewGuidingRequest $request): array
    {
        DB::beginTransaction();

        try {
            $isUpdate = $request->input('is_update') == '1';
            $originalStatus = null;

            $ownerUserId = $this->resolveGuidingOwnerUserId($request);

            if ($isUpdate && $request->input('guiding_id')) {
                $guiding = Guiding::findOrFail($request->input('guiding_id'));
                $this->assertCanModifyGuiding($guiding, $request);
                $originalStatus = $guiding->status;
            } else {
                $guiding = Guiding::where('user_id', $ownerUserId)
                    ->where('status', 2)
                    ->where('title', $request->input('title'))
                    ->where('city', $request->input('city'))
                    ->where('country', $request->input('country'))
                    ->where('region', $request->input('region'))
                    ->first();

                if (!$guiding) {
                    $guiding = new Guiding(['user_id' => $ownerUserId]);
                } else {
                    $this->assertCanModifyGuiding($guiding, $request);
                }
            }

            if (!$isUpdate) {
                $guiding->slug = slugify(
                    ($request->input('title') ?? 'temp') . '-in-' . ($request->input('location') ?? 'location')
                );
            }

            $pathsToDelete = $this->fillGuidingFromRequest($guiding, $request, true);

            $guiding->is_newguiding = 1;

            if ($isUpdate && ((int) $originalStatus === 1 || (int) $originalStatus === 0)) {
                $guiding->status = $originalStatus;
            } else {
                $guiding->status = 2;
            }

            $isAdminSubmit = str_contains($request->input('target_redirect', ''), 'admin')
                || auth('employees')->check();
            $this->rejectDomainDerivedSourceLanguage($guiding, $isUpdate, $isAdminSubmit);

            $guiding->save();
            $this->relocateGuidingMediaFromTemp($guiding);
            DB::commit();

            $this->deleteGuidingImagePaths($pathsToDelete, $guiding);

            return [
                'guiding_id' => $guiding->id,
                'gallery_images' => json_decode($guiding->gallery_images ?? '[]', true) ?? [],
                'thumbnail_path' => $guiding->thumbnail_path,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Block guidings.language from being set/changed based on the current website domain/locale.
     * Admin edit/create and profile save posts must not overwrite source language.
     */
    private function rejectDomainDerivedSourceLanguage(Guiding $guiding, bool $isUpdate, bool $isAdminSubmit): void
    {
        $locale = strtolower((string) (app()->getLocale() ?: config('app.locale') ?: ''));

        if ($isUpdate && $guiding->exists) {
            if ($guiding->isDirty('language')) {
                $guiding->language = $guiding->getOriginal('language');
            }

            return;
        }

        // New records from Admin: never stamp source language from the request domain/locale.
        if ($isAdminSubmit && $guiding->isDirty('language') && strtolower((string) $guiding->language) === $locale) {
            $guiding->language = null;
        }
    }

    /**
     * Prepare data for the job from request
     */
    private function prepareGuidingDataForJob(StoreNewGuidingRequest $request, array $processedData): array
    {
        // Basic fields
        $data = [
            'location' => $request->input('location', ''),
            'title' => $request->input('title', ''),
            'latitude' => $request->input('latitude', ''),
            'longitude' => $request->input('longitude', ''),
            'country' => $request->input('country', ''),
            'city' => $request->input('city', ''),
            'region' => $request->input('region', ''),
            
            // Processed images
            'gallery_images' => $processedData['gallery_images'],
            'thumbnail_path' => $processedData['thumbnail_path'],
            
            // Boat and fishing info
            'is_boat' => $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0,
            'fishing_from_id' => (int) $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2,
            'other_boat_info' => $request->input('other_boat_info', ''),
            'boat_type' => $request->input('type_of_boat', ''),
            
            // Process boat information
            'boat_information' => $this->prepareDescriptionsForJob($request),
            'boat_extras' => $this->prepareJsonDataForJob($request->input('boat_extras') ?? '[]'),
            
            // Target fish, methods, water types
            'target_fish' => $this->prepareJsonDataForJob($request->input('target_fish') ?? '[]'),
            'methods' => $this->prepareJsonDataForJob($request->input('methods') ?? '[]'),
            'style_of_fishing' => (int) $request->input('style_of_fishing', 3),
            'water_types' => $this->prepareJsonDataForJob($request->input('water_types') ?? '[]'),
            
            // Descriptions
            'desc_course_of_action' => $request->input('desc_course_of_action', ''),
            'desc_meeting_point' => $request->input('desc_meeting_point', ''),
            'meeting_point' => $request->input('meeting_point', ''),
            'desc_starting_time' => $request->input('desc_starting_time', ''),
            'desc_departure_time' => $request->input('desc_departure_time', []),
            'desc_tour_unique' => $request->input('desc_tour_unique', ''),
            'description' => $request->input('desc_course_of_action', $this->generateLongDescription($request)),
            
            // Requirements, recommendations, other info
            'requirements' => $this->prepareRequirementsForJob($request),
            'recommendations' => $this->prepareRecommendationsForJob($request),
            'other_information' => $this->prepareOtherInformationForJob($request),
            
            // Tour details
            'tour_type' => $request->input('tour_type', ''),
            'duration' => $request->input('duration', ''),
            'duration_value' => $request->input('duration') == 'multi_day' 
                ? (int) $request->input('duration_days', 0) 
                : (int) $request->input('duration_hours', 0),
            'no_guest' => (int) $request->input('no_guest', 0),
            'min_guests' => $request->has('has_min_guests') ? (int) $request->input('min_guests') : null,
            
            // Pricing
            'price_type' => $request->input('price_type', ''),
            'price' => $this->calculatePrice($request),
            'prices' => $this->preparePricesForJob($request),
            'inclusions' => $this->prepareJsonDataForJob($request->input('inclusions') ?? '[]'),
            'pricing_extra' => $this->preparePricingExtrasForJob($request),
            
            // Booking settings
            'allowed_booking_advance' => $request->input('allowed_booking_advance', ''),
            'booking_window' => $request->input('booking_window', ''),
            'seasonal_trip' => $request->input('seasonal_trip', ''),
            'months' => $request->input('months', []),
            'weekday_availability' => $request->input('weekday_availability', 'all_week'),
            'weekdays' => $request->input('weekday_availability') === 'all_week' 
                ? ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']
                : $request->input('weekdays', []),
        ];

        return $data;
    }

    /**
     * Helper methods for data preparation
     */
    private function prepareJsonDataForJob(?string $jsonString): array
    {
        if ($jsonString === null) {
            return [];
        }
        
        $data = collect(json_decode($jsonString, true) ?? []);
        return $data->map(function($item) {
            return $item['id'] ?? $item['value'] ?? $item;
        })->toArray();
    }

    private function prepareDescriptionsForJob($request): array
    {
        $descriptions = $request->input('descriptions', []);
        $descriptionData = [];

        foreach ($descriptions as $description) {
            $descriptionData[$description] = $request->input("boat_description_".$description);
        }

        return $descriptionData;
    }

    private function prepareRequirementsForJob($request): array
    {
        $requirements = $request->input('requiements_taking_part', []);
        $requirementData = [];

        foreach ($requirements as $requirement) {
            $requirementData[$requirement] = $request->input("requiements_taking_part_".$requirement);
        }

        return $requirementData;
    }

    private function prepareRecommendationsForJob($request): array
    {
        $recommendations = $request->input('recommended_preparation', []);
        $recommendationData = [];

        foreach ($recommendations as $recommendation) {
            $recommendationData[$recommendation] = $request->input("recommended_preparation_".$recommendation);
        }

        return $recommendationData;
    }

    private function prepareOtherInformationForJob($request): array
    {
        $otherInformations = $request->input('other_information', []);
        $otherInformationData = [];

        foreach ($otherInformations as $otherInformation) {
            $otherInformationData[$otherInformation] = $request->input("other_information_".$otherInformation);
        }

        return $otherInformationData;
    }

    private function calculatePrice($request): float
    {
        if ($request->input('price_type') === 'per_person') {
            return 0;
        }
        
        return (float) $request->input('price_per_boat', 0);
    }

    private function preparePricesForJob($request): array
    {
        $pricePerPerson = [];
        
        if ($request->input('price_type') === 'per_person') {
            foreach ($request->all() as $key => $value) {
                if (strpos($key, 'price_per_person_') === 0) {
                    $guestNumber = substr($key, strlen('price_per_person_'));
                    $pricePerPerson[] = [
                        'person' => $guestNumber,
                        'amount' => $value
                    ];
                }
            }
        } else {
            if($request->has('no_guest')){
                for ($i = 1; $i <= $request->input('no_guest', 0); $i++) {
                    $pricePerPerson[] = [
                        'person' => $i,
                        'amount' => (float) ($request->input('price_per_boat', 0) / max(1, $request->input('no_guest', 1))) * $i
                    ];
                }
            }
        }
        
        return $pricePerPerson;
    }

    private function preparePricingExtrasForJob($request): array
    {
        $pricingExtras = [];
        $i = 1;
        
        while (true) {
            $nameKey = "extra_name_" . $i;
            $priceKey = "extra_price_" . $i;

            if ($request->has($nameKey) && $request->has($priceKey)) {
                $extraPrice = \App\Models\ExtrasPrice::where('name', $request->input($nameKey))
                    ->orWhere('name_en', $request->input($nameKey))
                    ->first();
                $extraname = $extraPrice ? $extraPrice->name : $request->input($nameKey);
                
                if ($extraname && $request->input($priceKey)) {
                    $pricingExtras[] = [
                        'name' => $extraname,
                        'price' => $request->input($priceKey)
                    ];
                }
                $i++;
            } else {
                break;
            }
        }
        
        return $pricingExtras;
    }

    private function generateLongDescription($request)
    {
        $longDescriptions = json_decode(file_get_contents(public_path('assets/prompts/long_description.json')), true);
        $randomDescription = $longDescriptions['options'][array_rand($longDescriptions['options'])];

        $description = str_replace(
            ['{course_of_action}', '{meeting_point}', '{special_about}', '{tour_unique}', '{starting_time}'],
            [$request->desc_course_of_action, $request->desc_meeting_point, "", $request->desc_tour_unique, $request->desc_starting_time],
            $randomDescription['text']
        );

        return $description;
    }

    /**
     * Apply stored translations from Language table to a collection/array of guidings
     * for the current locale. Uses one batch query for all guidings to avoid N+1.
     * When translations exist, they are attached to the model's $translated property
     * so accessors like $guiding->title use them.
     *
     * @param iterable|\Illuminate\Support\Collection $guidings
     */
    private function applyGuidingTranslations($guidings): void
    {
        if (empty($guidings)) {
            return;
        }

        $locale = app()->getLocale() ?: config('app.locale');

        if (!$locale) {
            return;
        }

        $idsNeedingTranslation = [];
        $guidingsById = [];
        foreach ($guidings as $guiding) {
            if (!$guiding instanceof Guiding) {
                continue;
            }
            if ($guiding->language === $locale) {
                continue;
            }
            $idsNeedingTranslation[] = $guiding->id;
            $guidingsById[$guiding->id] = $guiding;
        }

        if (empty($idsNeedingTranslation)) {
            return;
        }

        $translationMap = $this->guidingTranslationService->getTranslatedGuidingsBatch(
            array_values(array_unique($idsNeedingTranslation)),
            $locale
        );

        foreach ($translationMap as $id => $translated) {
            if (isset($guidingsById[$id])) {
                $guidingsById[$id]->translated = $translated;
            }
        }
    }

    /**
     * Fill a Guiding model from request data.
     * Handles both draft and final save logic.
     *
     * @return array<int, string> Image paths to delete after a successful DB commit
     */
    private function fillGuidingFromRequest(Guiding $guiding, StoreNewGuidingRequest $request, bool $isDraft): array
    {
        // Step 1: Basic fields
        $guiding->location = $request->input('location', '');
        $guiding->title = $request->input('title', '');
        $guiding->lat = $request->input('latitude', '');
        $guiding->lng = $request->input('longitude', '');
        $guiding->country = $request->input('country', '');
        $guiding->city = $request->input('city', '');
        $guiding->region = $request->input('region', '');

        // Step 1: Images
        $pathsToDelete = [];
        $galeryImages = [];
        $imageListRawInput = $request->input('image_list');

        $normalizePath = fn ($path) => $this->galleryRetention->normalizePath($path);

        $retention = ['kept' => [], 'to_delete' => [], 'image_list_synced' => false, 'image_list' => []];
        if ($request->input('is_update') == '1') {
            $existingImages = json_decode($request->input('existing_images', '[]'), true) ?? [];
            $retention = $this->galleryRetention->retain($imageListRawInput, is_array($existingImages) ? $existingImages : []);
            $pathsToDelete = $retention['to_delete'];

            foreach ($retention['kept'] as $keptPath) {
                $normalizedExisting = $normalizePath($keptPath);
                if ($normalizedExisting) {
                    $galeryImages[$normalizedExisting] = $normalizedExisting;
                }
            }
        }

        $imageListNormalized = $retention['image_list_synced']
            ? $retention['image_list']
            : array_values(array_filter(array_map($normalizePath, (array) (json_decode((string) $imageListRawInput, true) ?? []))));
        $galeryImagesByBasename = [];
        foreach ($galeryImages as $normalized => $path) {
            $galeryImagesByBasename[basename($normalized)] = $path;
        }

        $basenameToPath = [];

        if ($request->has('title_image')) {
            $imageCount = count($galeryImages);
            $directory = media_listing_directory('guiding', $guiding->id > 0 ? (int) $guiding->id : null);
            $processedUploadKeys = [];

            foreach ($request->file('title_image') as $index => $image) {
                $uploadKey = $image->getClientOriginalName() . '|' . $image->getSize();

                if (in_array($uploadKey, $processedUploadKeys, true)) {
                    continue;
                }

                $index = $index + $imageCount;
                $webpPath = media_upload($image, $directory, $guiding->slug . "-" . $index . "-" . time(), 75, $guiding->id);
                $webpPath = ltrim($webpPath, '/');
                $normalizedNew = $normalizePath($webpPath);

                if ($normalizedNew) {
                    $galeryImages[$normalizedNew] = $webpPath;
                    $galeryImagesByBasename[basename($normalizedNew)] = $webpPath;
                    $basenameToPath[$image->getClientOriginalName()] = $webpPath;
                    $processedUploadKeys[] = $uploadKey;
                }
            }
        }

        if (empty($galeryImages) && !empty($imageListNormalized) && $retention['image_list_synced']) {
            foreach ($imageListNormalized as $normalizedPath) {
                $galeryImages[$normalizedPath] = $normalizedPath;
            }
        }

        if (!empty($galeryImages)) {
            $orderedGallery = [];

            if (!empty($imageListNormalized)) {
                foreach ($imageListNormalized as $normalizedPath) {
                    if (isset($galeryImages[$normalizedPath])) {
                        $orderedGallery[] = $galeryImages[$normalizedPath];
                        unset($galeryImages[$normalizedPath]);
                    } elseif (isset($galeryImagesByBasename[basename($normalizedPath)])) {
                        $keptPath = $galeryImagesByBasename[basename($normalizedPath)];
                        $orderedGallery[] = $keptPath;
                        $keptNormalized = $normalizePath($keptPath);
                        if ($keptNormalized) {
                            unset($galeryImages[$keptNormalized]);
                        }
                    } elseif (isset($basenameToPath[$normalizedPath])) {
                        $orderedGallery[] = $basenameToPath[$normalizedPath];
                        $uploadedNormalized = $normalizePath($basenameToPath[$normalizedPath]);
                        if ($uploadedNormalized) {
                            unset($galeryImages[$uploadedNormalized]);
                        }
                    } else {
                        $uploadBasename = basename($normalizedPath);
                        if (isset($basenameToPath[$uploadBasename])) {
                            $orderedGallery[] = $basenameToPath[$uploadBasename];
                            $uploadedNormalized = $normalizePath($basenameToPath[$uploadBasename]);
                            if ($uploadedNormalized) {
                                unset($galeryImages[$uploadedNormalized]);
                            }
                        }
                    }
                }
            }

            if (!empty($galeryImages)) {
                $orderedGallery = array_merge($orderedGallery, array_values($galeryImages));
            }

            $orderedGallery = array_values(array_filter($orderedGallery));

            if (!empty($orderedGallery)) {
                $primaryImageIndex = (int) $request->input('primaryImage', 0);
                if (isset($orderedGallery[$primaryImageIndex])) {
                    $guiding->thumbnail_path = $orderedGallery[$primaryImageIndex];
                } else {
                    $guiding->thumbnail_path = $orderedGallery[0];
                }

                $guiding->gallery_images = json_encode($orderedGallery);
            }
        }

        $committedGallery = json_decode($guiding->gallery_images ?? '[]', true) ?? [];
        if (is_string($guiding->thumbnail_path) && $guiding->thumbnail_path !== '') {
            $committedGallery[] = $guiding->thumbnail_path;
        }
        $pathsToDelete = $this->galleryRetention->filterDeletesAgainstCommitted($pathsToDelete, $committedGallery);

        // Step 2: Boat and fishing info
        $guiding->is_boat = $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 0) : 0;
        $guiding->fishing_from_id = (int) $request->has('type_of_fishing') ? ($request->input('type_of_fishing') == 'boat' ? 1 : 2) : 2;
        $guiding->additional_information = $request->input('other_boat_info', '');
        if ($guiding->is_boat) {
            $guiding->boat_type = $request->input('type_of_boat', '');
            $guiding->boat_information = $this->saveDescriptions($request);

            $boatExtrasData = collect(json_decode($request->input('boat_extras', '[]')));
            $boatExtras = $boatExtrasData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->boat_extras = json_encode($boatExtras);
        }

        // Step 3: Target fish, methods, water types
        if ($request->has('target_fish')) {
            $targetFishData = collect(json_decode($request->input('target_fish', '[]')));
            $targetFish = $targetFishData->map(function($item) {
                return $item->id ?? $item->value;
            })->toArray();
            $guiding->target_fish = json_encode($targetFish);
        }

        $methodsData = collect(json_decode($request->input('methods', '[]')));
        $methods = $methodsData->map(function($item) {
            return $item->id ?? $item->value;
        })->toArray();
        $guiding->fishing_methods = json_encode($methods);

        $guiding->fishing_type_id = (int) $request->input('style_of_fishing', 3);

        $waterTypesData = collect(json_decode($request->input('water_types', '[]')));
        $waterTypes = $waterTypesData->map(function($item) {
            return $item->id ?? $item->value;
        })->toArray();
        $guiding->water_types = json_encode($waterTypes);

        // Step 4: Descriptions - Only update if provided in current request
        if ($request->has('desc_course_of_action')) {
            $guiding->desc_course_of_action = $request->input('desc_course_of_action', '');
        }
        if ($request->has('desc_meeting_point')) {
            $guiding->desc_meeting_point = $request->input('desc_meeting_point', '');
        }
        if ($request->has('meeting_point')) {
            $guiding->meeting_point = $request->input('meeting_point', '');
        }
        if ($request->has('desc_starting_time')) {
            $guiding->desc_starting_time = $request->input('desc_starting_time', '');
        }
        $departureTimeInput = $request->input('desc_departure_time');
        $hasDepartureTime = $request->has('desc_departure_time');
        
        // Only update departure time if it's provided in the current request
        // Otherwise, preserve existing data (important for draft saves)
        if ($hasDepartureTime) {
            $guiding->desc_departure_time = json_encode($departureTimeInput);
        }
        // If not provided, keep existing value (don't overwrite with empty array)
        if ($request->has('desc_tour_unique')) {
            $guiding->desc_tour_unique = $request->input('desc_tour_unique', '');
        }
        $guiding->description = $request->input('desc_course_of_action', $this->generateLongDescription($request));

        // Step 5: Requirements, recommendations, other info
        $guiding->requirements = $this->saveRequirements($request);
        $guiding->recommendations = $this->saveRecommendations($request);
        $guiding->other_information = $this->saveOtherInformation($request);

        // Step 6: Tour type, duration, guests, price
        $guiding->tour_type = $request->input('tour_type', '');
        $guiding->duration_type = $request->input('duration', '');
        if ($request->input('duration') == 'multi_day') {
            $guiding->duration = (int) $request->input('duration_days', 0);
        } else {
            $guiding->duration = (int) $request->input('duration_hours', 0);
        }
        $guiding->max_guests = (int) $request->input('no_guest', 0);

        if ($request->has('price_type')) {
            $guiding->price_type = $request->input('price_type');
            $pricePerPerson = [];
            if ($request->input('price_type') === 'per_person') {
                foreach ($request->all() as $key => $value) {
                    if (strpos($key, 'price_per_person_') === 0) {
                        $guestNumber = substr($key, strlen('price_per_person_'));
                        $pricePerPerson[] = [
                            'person' => $guestNumber,
                            'amount' => $value
                        ];
                    }
                }
                $guiding->price = 0;
                $has_min_guests = $request->has('has_min_guests') ? 1 : 0;
                if ($has_min_guests) {
                    $guiding->min_guests = (int) $request->input('min_guests');
                } else {
                    $guiding->min_guests = null;
                }
            } else {
                if($request->has('no_guest')){
                    for ($i = 1; $i <= $request->input('no_guest', 0); $i++) {
                        $pricePerPerson[] = [
                            'person' => $i,
                            'amount' => (float) ($request->input('price_per_boat', 0) / max(1, $request->input('no_guest', 1))) * $i
                        ];
                    }
                }
                $guiding->price = (float) $request->input('price_per_boat', 0);
            }
            $guiding->prices = json_encode($pricePerPerson);
        }

        $inclusionsData = collect(json_decode($request->input('inclusions', '[]')));
        $inclusions = $inclusionsData->map(function($item) {
            return $item->id ?? $item->value;
        })->toArray();
        $guiding->inclusions = json_encode($inclusions);

        $pricingExtras = [];
        $i = 1;
        while (true) {
            $nameKey = "extra_name_" . $i;
            $priceKey = "extra_price_" . $i;

            $extraPrice = ExtrasPrice::where('name', $request->input($nameKey))
                ->orWhere('name_en', $request->input($nameKey))
                ->first();
            $extraname = $extraPrice ? $extraPrice->name : $request->input($nameKey);
            if ($request->has($nameKey) && $request->has($priceKey)) {
                if ($extraname && $request->input($priceKey)) {
                    $pricingExtras[] = [
                        'name' => $extraname,
                        'price' => $request->input($priceKey)
                    ];
                }
                $i++;
            } else {
                break;
            }
        }
        $guiding->pricing_extra = json_encode($pricingExtras);

        // Step 7: Booking/seasonal info
        $guiding->allowed_booking_advance = $request->input('allowed_booking_advance', '');
        $guiding->booking_window = $request->input('booking_window', '');

        if ($request->has('seasonal_trip')) {
            $guiding->seasonal_trip = $request->input('seasonal_trip');
            $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

            if ($request->input('seasonal_trip') == "season_monthly") {
                $selectedMonths = $request->input('months', []);
                $guiding->months = json_encode($selectedMonths);
            } else {
                $selectedMonths = $allMonths;
                $guiding->months = json_encode($selectedMonths);
            }
        }

        // Only update weekday availability if it's provided in the current request
        // Otherwise, preserve existing data (important for draft saves)
        if ($request->has('weekday_availability')) {
            $guiding->weekday_availability = $request->input('weekday_availability');
            if ($request->input('weekday_availability') === 'all_week') {
                $guiding->weekdays = json_encode(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
            } else {
                $guiding->weekdays = $request->has('weekdays') ? json_encode($request->input('weekdays')) : json_encode([]);
            }
        }
        // If not provided, keep existing values (don't overwrite)

        return $pathsToDelete;
    }

    /**
     * Build gallery state from the request. Queues removed images for deferred deletion.
     *
     * @return array<int, string>
     */
    private function processGuidingImages(Guiding $guiding, StoreNewGuidingRequest $request): array
    {
        $pathsToDelete = [];
        $imageListRaw = json_decode($request->input('image_list', '[]'), true) ?? [];
        $imageListNormalized = array_values(array_filter(array_map(
            fn ($path) => $this->normalizeGuidingImagePath($path),
            (array) $imageListRaw
        )));

        if ($this->shouldSkipGuidingImageProcessing($guiding, $request, $imageListNormalized)) {
            return $pathsToDelete;
        }

        $galeryImages = [];
        $processedFilenames = [];
        $imageListLookup = array_flip($imageListNormalized);

        if ($request->input('is_update') == '1') {
            $existingImages = json_decode($request->input('existing_images', '[]'), true) ?? [];

            foreach ($existingImages as $existingImage) {
                $normalizedExisting = $this->normalizeGuidingImagePath($existingImage);

                if (!$normalizedExisting) {
                    continue;
                }

                if (isset($imageListLookup[$normalizedExisting])) {
                    $galeryImages[$normalizedExisting] = ltrim((string) $existingImage, '/');
                    $processedFilenames[] = basename($normalizedExisting);
                } else {
                    $pathsToDelete[] = $existingImage;
                }
            }
        }

        if ($request->has('title_image')) {
            $uploadSlug = $guiding->slug ?: slugify(
                ($request->input('title') ?? 'temp') . '-in-' . ($request->input('location') ?? 'location')
            );
            $imageCount = count($galeryImages);
            $keptBasenames = array_flip(array_map('basename', array_values($galeryImages)));
            $maxNewUploads = $request->input('is_update') == '1'
                ? max(0, count($imageListNormalized) - $imageCount)
                : count($imageListNormalized);
            $newUploadCount = 0;

            foreach ($request->file('title_image') as $index => $image) {
                $originalFilename = $image->getClientOriginalName();

                if (isset($keptBasenames[$originalFilename]) || in_array($originalFilename, $processedFilenames, true)) {
                    continue;
                }

                if ($request->input('is_update') == '1' && $newUploadCount >= $maxNewUploads) {
                    continue;
                }

                $uploadIndex = $imageCount + $newUploadCount;
                $newUploadCount++;
                $webpPath = media_upload(
                    $image,
                    'guidings-images',
                    $uploadSlug . '-' . $uploadIndex . '-' . time(),
                    75,
                    $guiding->id
                );
                $webpPath = ltrim($webpPath, '/');
                $normalizedNew = $this->normalizeGuidingImagePath($webpPath);

                if ($normalizedNew) {
                    $galeryImages[$normalizedNew] = $webpPath;
                    $processedFilenames[] = $originalFilename;
                }
            }
        }

        if (empty($galeryImages) && !empty($imageListNormalized)) {
            foreach ($imageListNormalized as $normalizedPath) {
                if (str_starts_with($normalizedPath, 'guidings-images/')) {
                    $galeryImages[$normalizedPath] = $normalizedPath;
                }
            }
        }

        if (!empty($galeryImages)) {
            $orderedGallery = [];

            if (!empty($imageListNormalized)) {
                foreach ($imageListNormalized as $normalizedPath) {
                    if (isset($galeryImages[$normalizedPath])) {
                        $orderedGallery[] = $galeryImages[$normalizedPath];
                        unset($galeryImages[$normalizedPath]);
                    }
                }
            }

            if (!empty($galeryImages)) {
                $orderedGallery = array_merge($orderedGallery, array_values($galeryImages));
            }

            $seenGalleryPaths = [];
            $orderedGallery = array_values(array_filter($orderedGallery, function ($path) use (&$seenGalleryPaths) {
                $normalized = $this->normalizeGuidingImagePath($path);
                if (!$normalized || isset($seenGalleryPaths[$normalized])) {
                    return false;
                }
                $seenGalleryPaths[$normalized] = true;

                return true;
            }));

            if (!empty($orderedGallery)) {
                $primaryImageIndex = (int) $request->input('primaryImage', 0);
                if (isset($orderedGallery[$primaryImageIndex])) {
                    $guiding->thumbnail_path = $orderedGallery[$primaryImageIndex];
                } else {
                    $guiding->thumbnail_path = $orderedGallery[0];
                }

                $guiding->gallery_images = json_encode($orderedGallery);
            }
        }

        return $pathsToDelete;
    }

    private function saveDescriptions( $request)
    {
        $descriptions = $request->input('descriptions', []);
        $descriptionData = [];

        foreach ($descriptions as $description) {
            $descriptionData[$description] = $request->input("boat_description_".$description);
        }

        return json_encode($descriptionData);

    }

    private function saveOtherInformation($request)
    {
        $otherInformations = $request->input('other_information', []);
        $otherInformationData = [];

        foreach ($otherInformations as $otherInformation) {
            $otherInformationData[$otherInformation] = $request->input("other_information_".$otherInformation);
        }

        return json_encode($otherInformationData);
    }

    private function saveRequirements($request)
    {
        $requirements = $request->input('requiements_taking_part', []);
        $requirementData = [];

        foreach ($requirements as $requirement) {
            $requirementData[$requirement] = $request->input("requiements_taking_part_".$requirement);
        }

        return json_encode($requirementData);
    }

    private function saveRecommendations($request)
    {
        $recommendations = $request->input('recommended_preparation', []);
        $recommendationData = [];

        foreach ($recommendations as $recommendation) {
            $recommendationData[$recommendation] = $request->input("recommended_preparation_".$recommendation);
        }

        return json_encode($recommendationData);
    }

    public function store(StoreGuidingRequest $request)
    {
        $data = $request->validated();

        $data['user_id'] = auth()->id();
        $data['slug'] = slugify($data['title'] . "-in-" . $data['location']);
        // TODO Hier muss mehr abgefangen werden und umgebaut werden!
        $waters = $request->water;
        array_unshift($waters, 'alle');
        $data['water'] = serialize($waters);
        $targets = $request->targets;
        array_unshift($targets, 'alle');
        $data['targets'] = serialize($targets);
        $methods = $request->methods;
        array_unshift($methods, 'alle');
        $data['methods'] = serialize($methods);

        // Add Gebühren
        if($data['price_two_persons'] > 0) {
            $data['price_two_persons'];
        }
        if($data['price_three_persons'] > 0) {
            $data['price_three_persons'];
        }
        if($data['price_four_persons'] > 0) {
            $data['price_four_persons'];
        }
        if($data['price_five_persons'] > 0) {
            $data['price_five_persons'];
        }
        $guiding = Guiding::create($data);


        if($request->gallery) {
            foreach ($request->gallery as $key => $file) {

     
                if(isset($file['image_name'])) {
                    $maxFileSizeInBytes = 20971520;
                    $fileSizeInBytes = $file['image_name']->getSize();
                    if ($fileSizeInBytes > $maxFileSizeInBytes) {
                        return redirect()->back()->withErrors(['file' => 'Die Datei ist zu groß. Maximalgröße: 20MB']);
                    }
                    $name = time().rand(1,50).'.'.$file['image_name']->extension();
                    $file['image_name']->move(public_path('files'), $name);
                    $gallery = new Gallery();
                    $gallery->image_name = $name;
                    $gallery->user_id = auth()->user()->id;
                    $gallery->avatar = isset($file['avatar']) && $file['avatar'] == "on" ? 1 : 0;
                    $gallery->guiding_id = $guiding->id;
                    $gallery->save();
                }
            }
        }
    }
    
    public function show($id,$slug)
    {   
        $guiding = Guiding::where('id', $id)->where('slug', $slug)->publiclyVisible()->first();

        if(!$guiding){
            abort(404);
        }

        $guiding->healThumbnailPath();
        
        $targetId = $guiding->guidingTargets->pluck('id')->toArray();
        $fishingfrom = $guiding->fishingFrom->id;
        $fishingtype = $guiding->fishingTypes->id;

        $ratings = $guiding->user->received_ratings;
        $ratingCount = $ratings->count();
        $averageRating = $ratingCount > 0 ? $ratings->avg('rating') : 0;
        $otherGuidings = Guiding::whereHas('guidingTargets',function($query) use ($targetId){
            $query->wherein('target_id',$targetId);
        })->whereHas('fishingFrom',function($query) use($fishingfrom){
            $query->where('id',$fishingfrom);
        })->whereHas('fishingTypes',function($query) use($fishingtype){
            $query->where('id',$fishingtype);
        })
        ->publiclyVisible()
        ->limit(4)
        ->get();

        $this->applyGuidingTranslations(collect([$guiding]));
        $this->applyGuidingTranslations($otherGuidings);

        return view('pages.guidings.show', [
            'guiding' => $guiding,
            'ratings' => $ratings,
            'other_guidings' => $otherGuidings,
            'average_rating' => $averageRating,
        ]);
    }

    public function redirectLegacyShow(int $id, string $slug, Request $request)
    {
        $guiding = Guiding::query()->where('id', $id)->first()
            ?? Guiding::query()->where('slug', $slug)->first();

        if (! $guiding || blank($guiding->slug)) {
            abort(404);
        }

        return redirect()->route(
            'guidings.show',
            array_merge(['slug' => $guiding->slug], $request->query()),
            301
        );
    }

    public function redirectToNewFormat($slug)
    {
        $guiding = Guiding::where('slug',$slug)->first();

        if(!$guiding){
            abort(404);
        }

        return redirect()->route('guidings.show', ['slug' => $guiding->slug], 301);
    }


    public function edit(Guiding $guiding)
    {
        $this->assertCanModifyGuiding($guiding);

        $targets = Target::all();
        $methods = Method::all();
        $waters = Water::all();

        return view('pages.guidings.edit', compact('guiding','targets', 'methods', 'waters'));
    }

    public function edit_newguiding(Guiding $guiding)
    {
        // Frontend edit is owner-only. Admins edit via admin.guidings.edit.
        // Do not bypass on employees session here — shared admin+guide browser
        // sessions were incorrectly allowing Guide A to open Guide B's form.
        if ((int) $guiding->user_id !== (int) auth('web')->id()) {
            abort(403, 'Unauthorized action.');
        }

        $guiding->load([
            'guidingTargets', 'guidingWaters', 'guidingMethods', 
            'fishingTypes', 'fishingFrom'
        ]);

        // Prepare data for the form
        $formData = [
            'id' => $guiding->id,
            'is_update' => 1,
            'status' => $guiding->status,
            'user_id' => $guiding->user_id,
            //step1
            'title' => $guiding->title,
            'location' => $guiding->location,
            'latitude' => $guiding->lat,
            'longitude' => $guiding->lng,
            'country' => $guiding->country,
            'city' => $guiding->city,
            'region' => $guiding->region,
            'gallery_images' => $guiding->gallery_images,
            'thumbnail_path' => $guiding->thumbnail_path,

            //step 2
            'type_of_fishing' => $guiding->is_boat ? 'boat' : 'shore',
            'boat_type' => $guiding->boat_type,
            'boat_information' => $guiding->getBoatInformationAttribute(),
            'boat_extras' => $guiding->getBoatExtras(),

            //step 3
            'target_fish' => $guiding->getTargetFishNames(),
            'methods' => $guiding->getFishingMethodNames(),
            'water_types' => $guiding->getWaterNames(),

            //step 4
            'inclusions' => $guiding->getInclusionNames(),
            'fishing_type' => $guiding->fishing_type_id,

            //step 5
            'long_description' => $guiding->description,
            'desc_course_of_action' => $guiding->desc_course_of_action,
            'desc_starting_time' => $guiding->desc_starting_time,
            'desc_departure_time' => ($departureTimes = json_decode($guiding->desc_departure_time, true)),
            'desc_meeting_point' => $guiding->desc_meeting_point,
            'desc_tour_unique' => $guiding->desc_tour_unique,
            
            //step 6
            'requirements' => $guiding->getRequirementsAttribute(),
            'recommendations' => $guiding->getRecommendationsAttribute(),
            'other_information' => $guiding->getOtherInformationAttribute(),

            //step 7
            'tour_type' => trim($guiding->tour_type),
            'duration' => $guiding->duration,
            'duration_type' => $guiding->duration_type,
            'no_guest' => $guiding->max_guests,
            'min_guests' => $guiding->min_guests,
            'price_type' => $guiding->price_type,
            'price' => $guiding->price,
            'prices' => json_decode($guiding->prices, true),
            'pricing_extra' => $guiding->getPricingExtraAttribute(),

            //step 8
            'allowed_booking_advance' => $guiding->allowed_booking_advance,
            'booking_window' => $guiding->booking_window,
            'seasonal_trip' => $guiding->seasonal_trip,
            'months' => json_decode($guiding->months, true),
            'other_boat_info' => $guiding->additional_information,
            'weekday_availability' => $guiding->weekday_availability,
            'weekdays' => json_decode($guiding->weekdays, true),
        ];

        $locale = Config::get('app.locale');
        $nameField = $locale == 'en' ? 'name_en' : 'name';

        $modelClasses = [
            'targets' => Target::class,
            'methods' => Method::class,
            'waters' => Water::class,
            'inclusions' => Inclussion::class,
            'boat_extras' => BoatExtras::class,
            'extras_prices' => ExtrasPrice::class,
            'guiding_boat_types' => GuidingBoatType::class,
            'guiding_boat_descriptions' => GuidingBoatDescription::class,
            'guiding_additional_infos' => GuidingAdditionalInformation::class,
            'guiding_requirements' => GuidingRequirements::class,
            'guiding_recommendations' => GuidingRecommendations::class
        ];

        $collections = [];
        foreach ($modelClasses as $key => $modelClass) {
            $collections[$key] = $modelClass::all()
            ->map(function($item) use ($nameField, $key) {
                return [
                    'value' => $item->$nameField,
                    'id' => $item->id
                ];

            });
        }

        $pageTitle = __('profile.editguiding');

        return view('pages.profile.newguiding', array_merge(
            ['formData' => $formData, 'pageTitle' => $pageTitle],
            $collections
        ));
    }

    public function update(StoreGuidingRequest $request, Guiding $guiding)
    {
        $this->assertCanModifyGuiding($guiding);

        $files = $request->files;

        $guiding->update([
            'title' => $request->title,
            'slug' => slugify($request->title),
            'location' => $request->location,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'recommended_for_anfaenger' => $request->recommended_for_anfaenger,
            'recommended_for_fortgeschrittene' => $request->recommended_for_fortgeschrittene,
            'recommended_for_profis' => $request->recommended_for_profis,
            'duration' => $request->duration,
            'special_license_needed' => $request->special_license_needed,
            'required_special_license' => $request->required_special_license,
            'fishing_type' => $request->fishing_type,
            'fishing_from' => $request->fishing_from,
            'water_sonstiges' => $request->water_sonstiges,
            'target_fish_sonstiges' => $request->target_fish_sonstiges,
            'water' => serialize($request->water),
            'methods' => serialize($request->methods),
            'targets' => serialize($request->targets),
            'methods_sonstiges' => $request->methods_sonstiges,
            'water_name' => $request->water_name,
            'description' => $request->description,
            'required_equipment' => $request->required_equipment,
            'needed_equipment' => $request->needed_equipment,
            'meeting_point' => $request->meeting_point,
            'additional_information' => $request->additional_information,
            'catering' => $request->catering,
            'max_guests' => $request->max_guests,
            'price' => $request->price,
            'price_two_persons' => $request->price_two_persons,
            'price_three_persons' => $request->price_three_persons,
            'price_four_persons' => $request->price_four_persons,
            'price_five_persons' => $request->price_five_persons,
        ]);

        $images = app('guiding')->getImagesUrl($guiding);
        $imgKey = 'image_0';
        for($i=0;$i<=4;$i++){
            if(!isset($images['image_'.$i])){
                $imgKey = 'image_'.$i;
                break;
            }
        }
    
        foreach($files as $file){
            foreach($file as $index => $f){
                app('asset')->uploadImage($guiding,$imgKey,$f);
            }
  
        }

        return redirect()->back()->with(['message' => 'Das Guiding wurde erfolgreich bearbeitet!']);
    }

    public function deleteImage(Guiding $guiding, $img)
    {
        $this->assertCanModifyGuiding($guiding);

        app('asset')->deleteThumbnails($guiding, $img);
        app('asset')->deleteImage($guiding, $img);
    }

    public function deleteguiding($id)
    {
        $guiding = Guiding::find($id);
        if (!$guiding) {
            return redirect()->back()->with('error', 'Guiding not found');
        }

        $isOwner = auth('web')->check() && (int) $guiding->user_id === (int) auth('web')->id();
        $isAdmin = auth('employees')->check();

        if ($isOwner || $isAdmin) {
            if ($guiding->status == 1)
                $guiding->status = 0;
            else {
                $guiding->status = 1;
            }
            $guiding->save();
            return redirect()->back()->with('message', "Das Guiding wurde erfolgreich deaktiviert");
        }

        return redirect()->back()->with('error', 'Du hast keine Berechtigung das Guiding zu löschen.. bitte wende Dich an einen Administrator');
    }

    // request
    public function bookingrequest(){
        return view('pages.guidings.search-request');
    }

    public function bookingRequestStore(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required',
            'g-recaptcha-response' => \App\Rules\Recaptcha::production(),
        ]);

        $guideRequest = new GuidingRequest;
        
        $guideRequest->guide_type = $request->guideType;
        $guideRequest->rentaboat = $request->rentaboat;
        $guideRequest->fishing_duration = $request->fishingDuration;

        $guideRequest->country = $request->country;
        $guideRequest->city = $request->city;
        $guideRequest->days_of_tour = $request->tripDays;
        $guideRequest->days_of_fishing = $request->daysOfFishing;
        $guideRequest->accomodation = $request->accomodation;
        $guideRequest->targets = json_encode($request->target_fish);
        $guideRequest->methods = json_encode($request->methods);
        $guideRequest->fishing_from = $request->fishing_from;
        $guideRequest->number_of_guest = $request->numberofguest;
        $guideRequest->from_date = date("Y-m-d", strtotime($request->date_of_tour_from));  
        $guideRequest->to_date = date("Y-m-d", strtotime($request->date_of_tour_to));  
        $guideRequest->name = $request->name;
        $guideRequest->phone = $request->phone;
        $guideRequest->email = $request->email;

        $guideRequest->save();

        if($request->locale == 'en'){
            \App::setLocale('en');
        }else{
            \App::setLocale('de');
        }
        $email = config('mail.admin_email');
        if (!CheckEmailLog('guiding_request_mail', 'guiding_request_mail', $email)) {
            Mail::to($email)->queue(new GuidingRequestMail($guideRequest));
        }
        if (!CheckEmailLog('search_request_user_mail', 'search_request_user_mail', $request->email)) {
            Mail::to($request->email)->queue(new SearchRequestUserMail($guideRequest));
        }

        return redirect()->back()->with('message', "Email Has been Sent");
    }

    /**
     * Alternative synchronous version of saveDraft for immediate feedback
     * Use this if you need immediate response with guiding_id
     */
    public function saveDraftSync(StoreNewGuidingRequest $request)
    {
        try {
            $result = $this->persistGuidingDraft($request);
            $guiding = Guiding::findOrFail($result['guiding_id']);

            try {
                $this->syncGuidingCalendarSchedule($guiding, $request);
            } catch (\Exception $calendarException) {
                Log::warning('Calendar schedule sync failed after draft save', [
                    'guiding_id' => $guiding->id,
                    'error' => $calendarException->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'guiding_id' => $result['guiding_id'],
                'message' => 'Draft saved successfully.',
                'gallery_images' => $result['gallery_images'],
                'thumbnail_path' => $result['thumbnail_path'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in saveDraftSync: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to save draft.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Rebuild calendar schedules after the guiding row is committed.
     * Skipped on intermediate draft step saves to avoid heavy concurrent writes.
     */
    private function syncGuidingCalendarSchedule(Guiding $guiding, StoreNewGuidingRequest $request): void
    {
        if (!$request->has('seasonal_trip')) {
            return;
        }

        $isDraft = $request->input('is_draft', 0) == 1;
        $currentStep = (int) $request->input('current_step', 0);
        if ($isDraft && $currentStep > 0 && $currentStep < 7) {
            return;
        }

        $allMonths = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];

        if ($request->input('seasonal_trip') === 'season_monthly') {
            $selectedMonths = $request->input('months', []);
        } else {
            $selectedMonths = $allMonths;
        }

        if (empty($selectedMonths)) {
            $selectedMonths = json_decode($guiding->months ?? '[]', true) ?? $allMonths;
        }

        $weekdays = $request->input('weekdays', []);
        if ($request->input('weekday_availability') === 'all_week') {
            $weekdays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        } elseif (empty($weekdays)) {
            $weekdays = json_decode($guiding->weekdays ?? '[]', true) ?? [];
        }

        CalendarScheduleService::generateCompleteSchedule(
            $guiding,
            $selectedMonths,
            $weekdays,
            $request->input('is_update') == '1'
        );
    }

    private function relocateGuidingMediaFromTemp(Guiding $guiding): void
    {
        if ($guiding->id <= 0) {
            return;
        }

        $gallery = json_decode($guiding->gallery_images ?? '[]', true) ?? [];
        if (empty($gallery) && empty($guiding->thumbnail_path)) {
            return;
        }

        $relocated = $this->mediaRelocator->promoteForListing(
            'guiding',
            (int) $guiding->id,
            [
                'gallery_images' => $gallery,
                'thumbnail_path' => $guiding->thumbnail_path,
            ]
        );

        $guiding->gallery_images = json_encode($relocated['gallery_images']);
        $guiding->thumbnail_path = $relocated['thumbnail_path'] ?: $guiding->thumbnail_path;
        $guiding->saveQuietly();
    }


}
