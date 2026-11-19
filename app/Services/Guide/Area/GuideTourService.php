<?php

namespace App\Services\Guide\Area;

use App\Enums\TourStatus;
use App\Models\Guiding;
use App\Models\User;
use App\Services\Guiding\GuidingSeoService;
use App\Services\Media\ListingMediaCopier;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Guide Area › My fishing tours (page and API): list by status, go live, deactivate, duplicate.
 * Status is the stored guidings.status read through TourStatus.
 */
class GuideTourService
{
    public const PER_PAGE = 12;

    /** Columns a tour card needs; keeps the list query light. */
    private const LIST_COLUMNS = [
        'id', 'user_id', 'title', 'slug', 'location', 'city', 'country', 'status', 'price', 'price_type',
        'thumbnail_path', 'gallery_images', 'voucher_enabled', 'duplicated_from_id', 'updated_at', 'created_at',
        // publishingGaps() reads these for drafts / deactivated tours:
        'target_fish', 'fishing_methods', 'water_types', 'fishing_type_id', 'desc_course_of_action',
        'desc_meeting_point', 'desc_tour_unique', 'desc_starting_time', 'tour_type', 'duration_type', 'duration',
        'max_guests', 'allowed_booking_advance', 'booking_window', 'seasonal_trip', 'is_boat', 'boat_type',
    ];

    /** Not copied by duplicate(): identity, publication state, media (copied separately). */
    private const NOT_DUPLICATED = [
        'id', 'slug', 'status', 'created_at', 'updated_at', 'thumbnail_id', 'thumbnail_path', 'galleries',
        'gallery_images', 'duplicated_from_id',
    ];

    public function __construct(
        private readonly GuidingSeoService $seo,
        private readonly ListingMediaCopier $media,
    ) {}

    public function paginate(User $guide, ?TourStatus $status = null, string $search = '', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        return $this->query($guide, $search)
            ->when($status !== null, fn (Builder $q) => $q->where('status', $status->value))
            ->orderByRaw('CASE status WHEN 1 THEN 0 WHEN 2 THEN 1 ELSE 2 END')
            ->latest('updated_at')
            ->paginate($perPage, self::LIST_COLUMNS)
            ->withQueryString();
    }

    /**
     * Tours per status for the tabs, plus "all".
     *
     * @return array{all: int, live: int, draft: int, deactivated: int}
     */
    public function counts(User $guide, string $search = ''): array
    {
        $byStatus = $this->query($guide, $search)
            ->selectRaw('status, COUNT(*) AS aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $counts = ['all' => 0];
        foreach (TourStatus::cases() as $case) {
            $counts[$case->key()] = (int) ($byStatus[$case->value] ?? 0);
            $counts['all'] += $counts[$case->key()];
        }

        return $counts;
    }

    /**
     * Publishes a draft or deactivated tour. Same rules as the tour editor: the guide must be
     * verified and the tour complete.
     *
     * @throws TourActionException
     */
    public function goLive(User $guide, Guiding $guiding): Guiding
    {
        if (! $guide->canPublishGuidings()) {
            throw new TourActionException(TourActionException::NOT_VERIFIED);
        }

        $gaps = $guiding->publishingGaps();
        if ($gaps !== []) {
            throw new TourActionException(TourActionException::INCOMPLETE, $gaps);
        }

        $guiding->status = TourStatus::Live->value;
        $guiding->save();

        return $guiding;
    }

    public function deactivate(Guiding $guiding): Guiding
    {
        // A draft stays a draft: "deactivated" means it was live once (or an admin took it down).
        if (TourStatus::fromGuiding($guiding) === TourStatus::Live) {
            $guiding->status = TourStatus::Deactivated->value;
            $guiding->save();
        }

        return $guiding;
    }

    /**
     * Draft copy of a tour with its own copies of the photos and the same options. Bookings,
     * reviews, translations and the calendar are not copied.
     */
    public function duplicate(Guiding $original): Guiding
    {
        $copy = DB::transaction(function () use ($original) {
            $copy = new Guiding;
            // Raw attributes: forceFill() would re-encode JSON-cast columns that are already JSON.
            $copy->setRawAttributes(collect($original->getAttributes())->except(self::NOT_DUPLICATED)->all());
            $copy->title = __('guide_area.tours.copy_title', ['title' => $original->title]);
            $copy->status = TourStatus::Draft->value;
            $copy->duplicated_from_id = $original->id;
            $copy->slug = $this->seo->generateSlug((string) $copy->title, (string) ($copy->location ?: 'location'));
            $copy->save();

            // Legacy pivots the old editors still write.
            foreach (['guidingTargets', 'guidingMethods', 'guidingWaters', 'inclussions', 'levels'] as $relation) {
                $ids = $original->{$relation}()->pluck($original->{$relation}()->getRelated()->getQualifiedKeyName())->all();
                if ($ids !== []) {
                    $copy->{$relation}()->sync($ids);
                }
            }

            foreach ($original->extras()->get() as $extra) {
                $copy->extras()->save($extra->replicate(['guiding_id']));
            }

            return $copy;
        });

        $gallery = $original->gallery_images;
        if (is_string($gallery)) {
            $gallery = json_decode($gallery, true) ?? [];
        }
        $media = $this->media->copyGallery('guiding', $copy->id, is_array($gallery) ? array_values($gallery) : [], $original->thumbnail_path);
        $copy->forceFill($media)->save();

        return $copy;
    }

    private function query(User $guide, string $search): Builder
    {
        $search = trim($search);

        return Guiding::query()
            ->where('user_id', $guide->id)
            ->when($search !== '', fn (Builder $q) => $q->where(fn (Builder $w) => $w
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('location', 'like', '%'.$search.'%')));
    }
}
