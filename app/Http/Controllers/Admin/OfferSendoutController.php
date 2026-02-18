<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Admin\OfferFollowUpMail;
use App\Mail\Admin\OfferSendoutMail;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\User;
use Carbon\Carbon;
use App\Models\EmailLog;
use App\Models\CustomCampOffer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfferSendoutController extends Controller
{
    /**
     * Display the offer builder / sendout page (create new offer).
     */
    public function create()
    {
        $customers = User::whereNotNull('email')
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname', 'email', 'phone']);

        $camps = Camp::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $accommodations = Accommodation::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'per_person_pricing', 'max_occupancy']);

        $boats = RentalBoat::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'prices', 'max_persons']);

        $guidings = Guiding::where('status', 1)
            ->orderBy('title')
            ->get(['id', 'title', 'slug', 'price', 'prices', 'price_type', 'max_guests']);

        // Prepare component data with prices and capacities for JavaScript
        $accommodationsData = $accommodations->map(function ($acc) {
            return [
                'id' => $acc->id,
                'title' => $acc->title,
                'price' => $this->getAccommodationBasePrice($acc),
                'capacity' => $acc->max_occupancy ?? 0,
            ];
        });

        $boatsData = $boats->map(function ($boat) {
            return [
                'id' => $boat->id,
                'title' => $boat->title,
                'price' => $this->getRentalBoatBasePrice($boat),
                'capacity' => $boat->max_persons ?? 0,
            ];
        });

        $guidingsData = $guidings->map(function ($guiding) {
            return [
                'id' => $guiding->id,
                'title' => $guiding->title,
                'price' => $this->getGuidingBasePrice($guiding),
                'capacity' => $guiding->max_guests ?? 0,
            ];
        });

        // Build camp-name lookups using existing Eloquent relationships on Camp.
        // One Camp query + 3 eager-load queries; no raw SQL needed.
        $accommodationIds = $accommodations->pluck('id')->all();
        $boatIds          = $boats->pluck('id')->all();
        $guidingIds       = $guidings->pluck('id')->all();

        $campsWithComponents = Camp::where('status', 'active')
            ->with([
                'accommodations' => fn ($q) => $q->select('accommodations.id')->whereIn('accommodations.id', $accommodationIds),
                'rentalBoats'    => fn ($q) => $q->select('rental_boats.id')->whereIn('rental_boats.id', $boatIds),
                'guidings'       => fn ($q) => $q->select('guidings.id')->whereIn('guidings.id', $guidingIds),
            ])
            ->get(['id', 'title']);

        $accMap     = [];
        $boatMap    = [];
        $guidingMap = [];

        foreach ($campsWithComponents as $camp) {
            foreach ($camp->accommodations as $acc) {
                $accMap[$acc->id][] = $camp->title;
            }
            foreach ($camp->rentalBoats as $boat) {
                $boatMap[$boat->id][] = $camp->title;
            }
            foreach ($camp->guidings as $guiding) {
                $guidingMap[$guiding->id][] = $camp->title;
            }
        }

        $accCampNames     = collect($accMap)->map(fn ($t) => implode(', ', $t));
        $boatCampNames    = collect($boatMap)->map(fn ($t) => implode(', ', $t));
        $guidingCampNames = collect($guidingMap)->map(fn ($t) => implode(', ', $t));

        // Pre-build whitelist arrays so @json() in the view receives simple variables.
        $fullOptionsAccommodations = $accommodations->map(fn ($a) => [
            'id'        => $a->id,
            'value'     => $a->title,
            'connected' => false,
            'camp_name' => $accCampNames->get($a->id, ''),
        ])->values()->all();

        $fullOptionsBoats = $boats->map(fn ($b) => [
            'id'        => $b->id,
            'value'     => $b->title,
            'connected' => false,
            'camp_name' => $boatCampNames->get($b->id, ''),
        ])->values()->all();

        $fullOptionsGuidings = $guidings->map(fn ($g) => [
            'id'        => $g->id,
            'value'     => $g->title,
            'connected' => false,
            'camp_name' => $guidingCampNames->get($g->id, ''),
        ])->values()->all();

        return view('admin.pages.offer-sendout.index', [
            'customers'              => $customers,
            'camps'                  => $camps,
            'accommodations'         => $accommodations,
            'boats'                  => $boats,
            'guidings'               => $guidings,
            'accommodationsData'     => $accommodationsData,
            'boatsData'              => $boatsData,
            'guidingsData'           => $guidingsData,
            'fullOptionsAccommodations' => $fullOptionsAccommodations,
            'fullOptionsBoats'          => $fullOptionsBoats,
            'fullOptionsGuidings'       => $fullOptionsGuidings,
        ]);
    }

    /**
     * Display a listing of saved custom camp offers (base page).
     */
    public function customCampOffers()
    {
        $customCampOffers = CustomCampOffer::with(['customer', 'creator'])
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.pages.offer-sendout.custom-camp-offers', [
            'customCampOffers' => $customCampOffers,
        ]);
    }

    /**
     * Update custom camp offer status (Approve, Follow up, Cancel).
     */
    public function updateStatus(Request $request, CustomCampOffer $customCampOffer)
    {
        $request->validate(['status' => ['required', Rule::in([CustomCampOffer::STATUS_ACCEPTED, CustomCampOffer::STATUS_FOLLOW_UP, CustomCampOffer::STATUS_REJECTED])]]);
        $customCampOffer->update(['status' => $request->status]);
        return response()->json(['success' => true, 'message' => 'Status updated.', 'status' => $customCampOffer->status]);
    }

    /**
     * Send a friendly follow-up email for a custom camp offer (CC CEO).
     */
    public function sendFollowUp(Request $request, CustomCampOffer $customCampOffer)
    {
        $email = $customCampOffer->recipient_email;
        if (empty($email)) {
            return response()->json([
                'success' => false,
                'message' => 'No recipient email for this offer.',
            ], 422);
        }

        $locale = $customCampOffer->locale ?? config('app.locale', 'en');
        if (! in_array($locale, ['en', 'de'])) {
            $locale = 'en';
        }

        $recipientName = $customCampOffer->recipient_name ?: $email;
        $offerSummary = $this->buildFollowUpOfferSummary($customCampOffer);
        $ceoEmail = config('mail.admin_email', config('mail.from.address'));

        $payload = [
            'recipient_name' => $recipientName,
            'offer_summary' => $offerSummary,
        ];

        try {
            Mail::to($email)
                ->locale($locale)
                ->cc($ceoEmail)
                ->send(new OfferFollowUpMail($payload));
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send follow-up email: ' . $e->getMessage(),
            ], 500);
        }

        $subject = __('emails.offer_followup_subject', ['name' => config('app.name')]);
        EmailLog::create([
            'email' => $email,
            'language' => $locale,
            'subject' => $subject,
            'type' => 'offer_followup',
            'status' => 1,
            'target' => 'offer_followup_' . $customCampOffer->id . '_' . now()->format('Y-m-d_H-i-s'),
            'additional_info' => json_encode([
                'recipient_name' => $recipientName,
                'cc' => $ceoEmail,
                'custom_camp_offer_id' => $customCampOffer->id,
            ]),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Follow-up email sent to ' . $email . ' (CC: CEO).',
        ]);
    }

    /**
     * Build a short one-line summary of the offer for the follow-up email.
     */
    private function buildFollowUpOfferSummary(CustomCampOffer $customCampOffer): string
    {
        $ro = $customCampOffer->resolvedOffers;
        if (empty($ro)) {
            return '';
        }
        $parts = [];
        foreach ($ro as $r) {
            $campTitle = $r['camp'] ? $r['camp']->title : null;
            $df = $r['date_from'] ?? null;
            $dt = $r['date_to'] ?? null;
            $dateStr = '';
            if ($df || $dt) {
                $dateStr = $df ? Carbon::parse($df)->format('d.m.Y') : '';
                if ($dt) {
                    $dateStr .= ($dateStr ? ' – ' : '') . Carbon::parse($dt)->format('d.m.Y');
                }
            }
            $line = $campTitle ?: __('emails.offer_sendout_offer');
            if ($dateStr) {
                $line .= ' · ' . $dateStr;
            }
            if (! empty($r['price'])) {
                $line .= ' · ' . $r['price'];
            }
            $parts[] = $line;
        }
        return implode(' | ', $parts);
    }

    /**
     * Get custom camp offer details (AJAX).
     */
    public function getCustomCampOffer(CustomCampOffer $customCampOffer)
    {
        $customCampOffer->load(['customer', 'creator']);

        $resolvedOffers = [];
        foreach ($customCampOffer->resolvedOffers as $ro) {
            $resolvedOffers[] = [
                'camp' => $ro['camp'] ? ['id' => $ro['camp']->id, 'title' => $ro['camp']->title] : null,
                'accommodations' => $ro['accommodations']->map(fn ($a) => ['id' => $a->id, 'title' => $a->title])->values()->all(),
                'boats' => $ro['boats']->map(fn ($b) => ['id' => $b->id, 'title' => $b->title])->values()->all(),
                'guidings' => $ro['guidings']->map(fn ($g) => ['id' => $g->id, 'title' => $g->title])->values()->all(),
                'date_from' => $ro['date_from'] ? Carbon::parse($ro['date_from'])->format('d.m.Y') : null,
                'date_to' => $ro['date_to'] ? Carbon::parse($ro['date_to'])->format('d.m.Y') : null,
                'number_of_persons' => $ro['number_of_persons'],
                'price' => $ro['price'],
                'additional_info' => $ro['additional_info'],
            ];
        }

        return response()->json([
            'success' => true,
            'offer' => [
                'id' => $customCampOffer->id,
                'name' => $customCampOffer->name,
                'recipient_type' => $customCampOffer->recipient_type,
                'recipient_name' => $customCampOffer->recipient_name,
                'recipient_email' => $customCampOffer->recipient_email,
                'recipient_phone' => $customCampOffer->recipient_phone,
                'camp' => $customCampOffer->camp ? ['id' => $customCampOffer->camp->id, 'title' => $customCampOffer->camp->title] : null,
                'date_from' => $customCampOffer->date_from ? Carbon::parse($customCampOffer->date_from)->format('d.m.Y') : null,
                'date_to' => $customCampOffer->date_to ? Carbon::parse($customCampOffer->date_to)->format('d.m.Y') : null,
                'number_of_persons' => $customCampOffer->number_of_persons,
                'price' => $customCampOffer->price,
                'additional_info' => $customCampOffer->additional_info,
                'free_text' => $customCampOffer->free_text,
                'resolved_offers' => $resolvedOffers,
                'offers' => $customCampOffer->offers,
                'status' => $customCampOffer->status ?? 'sent',
                'sent_at' => $customCampOffer->sent_at ? $customCampOffer->sent_at->format('d.m.Y H:i') : null,
                'created_by' => $customCampOffer->creator ? $customCampOffer->creator->firstname . ' ' . $customCampOffer->creator->lastname : null,
            ],
        ]);
    }

    /**
     * Return rendered email HTML for real-time preview (AJAX).
     */
    public function preview(Request $request)
    {
        $locale = $request->input('locale', app()->getLocale());
        if (in_array($locale, ['en', 'de'])) {
            app()->setLocale($locale);
        }
        $payload = $this->normalizePreviewPayload($request->all());
        $html = view('mails.admin.offer-sendout', $payload)->render();
        return response()->json(['html' => $html]);
    }

    /**
     * Send the offer email to the recipient and CC admin.
     */
    public function send(Request $request)
    {
        $rules = [
            'recipient_type' => ['required', Rule::in('customer', 'manual')],
            'customer_id' => ['required_if:recipient_type,customer', 'nullable', 'exists:users,id'],
            'manual_name' => ['nullable', 'string', 'max:255'],
            'manual_email' => ['required_if:recipient_type,manual', 'nullable', 'email'],
            'manual_phone' => ['nullable', 'string', 'max:255'],
            'camp_id' => ['nullable', 'exists:camps,id'],
            'accommodation_ids' => ['nullable', 'string', 'max:500'],
            'boat_ids' => ['nullable', 'string', 'max:500'],
            'guiding_ids' => ['nullable', 'string', 'max:500'],
            'date_from' => ['nullable', 'string', 'max:50'],
            'date_to' => ['nullable', 'string', 'max:50'],
            'number_of_persons' => ['nullable', 'string', 'max:50'],
            'price' => ['nullable', 'string', 'max:100'],
            'additional_info' => ['nullable', 'string'],
            'introduction_text' => ['nullable', 'string'],
            'free_text' => ['nullable', 'string'],
            'locale' => ['nullable', 'string', Rule::in(['en', 'de'])],
            'offers' => ['nullable', 'array'],
            'offers.*.camp_id' => ['nullable', 'exists:camps,id'],
            'offers.*.accommodation_ids' => ['nullable', 'string', 'max:500'],
            'offers.*.boat_ids' => ['nullable', 'string', 'max:500'],
            'offers.*.guiding_ids' => ['nullable', 'string', 'max:500'],
            'offers.*.date_from' => ['nullable', 'string', 'max:50'],
            'offers.*.date_to' => ['nullable', 'string', 'max:50'],
            'offers.*.number_of_persons' => ['nullable', 'string', 'max:50'],
            'offers.*.price' => ['nullable', 'string', 'max:100'],
            'offers.*.additional_info' => ['nullable', 'string'],
            'offers.*.accommodation_prices' => ['nullable', 'array'],
            'offers.*.accommodation_prices.*.id' => ['nullable'],
            'offers.*.accommodation_prices.*.title' => ['nullable', 'string'],
            'offers.*.accommodation_prices.*.price' => ['nullable', 'numeric'],
            'offers.*.accommodation_prices.*.qty' => ['nullable', 'numeric'],
            'offers.*.accommodation_prices.*.days' => ['nullable', 'numeric'],
            'offers.*.boat_prices' => ['nullable', 'array'],
            'offers.*.boat_prices.*.id' => ['nullable'],
            'offers.*.boat_prices.*.title' => ['nullable', 'string'],
            'offers.*.boat_prices.*.price' => ['nullable', 'numeric'],
            'offers.*.boat_prices.*.qty' => ['nullable', 'numeric'],
            'offers.*.boat_prices.*.days' => ['nullable', 'numeric'],
            'offers.*.guiding_prices' => ['nullable', 'array'],
            'offers.*.guiding_prices.*.id' => ['nullable'],
            'offers.*.guiding_prices.*.title' => ['nullable', 'string'],
            'offers.*.guiding_prices.*.price' => ['nullable', 'numeric'],
            'offers.*.guiding_prices.*.qty' => ['nullable', 'numeric'],
            'offers.*.guiding_prices.*.days' => ['nullable', 'numeric'],
        ];
        $validated = $request->validate($rules);

        $recipientEmail = null;
        $recipientName = null;

        if ($validated['recipient_type'] === 'customer' && !empty($validated['customer_id'])) {
            $customer = User::find($validated['customer_id']);
            if ($customer && $customer->email) {
                $recipientEmail = $customer->email;
                $recipientName = $customer->firstname . ' ' . $customer->lastname;
            }
        } elseif ($validated['recipient_type'] === 'manual' && !empty($validated['manual_email'])) {
            $recipientEmail = $validated['manual_email'];
            $recipientName = $validated['manual_name'] ?? $validated['manual_email'];
        }

        if (!$recipientEmail) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a customer or enter a valid email address.',
            ], 422);
        }

        $payload = $this->buildMailPayload($validated, $recipientName ?? '');
        $adminEmail = config('mail.admin_email', config('mail.from.address'));
        $locale = $validated['locale'] ?? config('app.locale', 'en');
        if (!in_array($locale, ['en', 'de'])) {
            $locale = 'en';
        }

        $subject = __('emails.offer_sendout_subject', ['name' => config('app.name')]);

        $messageId = null;
        try {
            $mailable = new OfferSendoutMail($payload);
            $messageId = $mailable->messageId;
            
            \Log::info('Sending offer email', [
                'to' => $recipientEmail,
                'cc' => $adminEmail,
                'locale' => $locale,
                'message_id' => $messageId,
            ]);
            
            Mail::to($recipientEmail)
                ->locale($locale)
                ->cc($adminEmail)
                ->send($mailable);
            
            \Log::info('Offer email sent successfully', [
                'to' => $recipientEmail,
                'message_id' => $messageId,
            ]);
        } catch (\Throwable $e) {
            \Log::error('Failed to send offer email', [
                'to' => $recipientEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }

        EmailLog::create([
            'email' => $recipientEmail,
            'language' => $locale,
            'subject' => $subject,
            'type' => 'offer_sendout',
            'status' => 1,
            'target' => 'offer_sendout_' . $recipientEmail . '_' . now()->format('Y-m-d_H-i-s'),
            'additional_info' => json_encode([
                'recipient_name' => $recipientName,
                'cc' => $adminEmail,
                'message_id' => $messageId,
            ]),
        ]);

        // Save the offer as a custom camp offer
        $this->saveCustomCampOffer($validated, $recipientEmail, $recipientName, $locale);

        return response()->json([
            'success' => true,
            'message' => 'Offer email sent successfully to ' . $recipientEmail . ' (CC: ' . $adminEmail . ').',
        ]);
    }

    /**
     * Build payload for the mailable and email view (catalog with one or more offers).
     */
    private function buildMailPayload(array $validated, string $recipientName): array
    {
        $offersInput = $validated['offers'] ?? null;
        if (is_array($offersInput) && count($offersInput) > 0) {
            $offers = [];
            foreach ($offersInput as $row) {
                $offers[] = $this->buildSingleOfferData($row);
            }
        } else {
            $offers = [$this->buildSingleOfferData([
                'camp_id' => $validated['camp_id'] ?? null,
                'accommodation_ids' => $validated['accommodation_ids'] ?? '',
                'boat_ids' => $validated['boat_ids'] ?? '',
                'guiding_ids' => $validated['guiding_ids'] ?? '',
                'accommodation_prices' => $validated['accommodation_prices'] ?? [],
                'boat_prices' => $validated['boat_prices'] ?? [],
                'guiding_prices' => $validated['guiding_prices'] ?? [],
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'number_of_persons' => $validated['number_of_persons'] ?? '',
                'price' => $validated['price'] ?? '',
                'additional_info' => $validated['additional_info'] ?? '',
            ])];
        }

        return [
            'recipient_name' => $recipientName,
            'introduction_text' => $validated['introduction_text'] ?? '',
            'free_text' => $validated['free_text'] ?? '',
            'offers' => $offers,
        ];
    }

    /**
     * Build one offer block for the catalog (camp highlight + details, no item images).
     * Merges component prices (accommodation_prices, boat_prices, guiding_prices) with loaded models.
     */
    private function buildSingleOfferData(array $input): array
    {
        $camp = !empty($input['camp_id']) ? Camp::find($input['camp_id']) : null;
        $accommodationIds = $this->parseIds($input['accommodation_ids'] ?? '');
        $boatIds = $this->parseIds($input['boat_ids'] ?? '');
        $guidingIds = $this->parseIds($input['guiding_ids'] ?? '');

        $accommodations = $accommodationIds ? Accommodation::with('accommodationType')->whereIn('id', $accommodationIds)->orderBy('title')->get() : collect();
        $boats = $boatIds ? RentalBoat::with('boatType')->whereIn('id', $boatIds)->orderBy('title')->get() : collect();
        $guidings = $guidingIds ? Guiding::whereIn('id', $guidingIds)->orderBy('title')->get() : collect();

        $accPrices = collect($input['accommodation_prices'] ?? [])->keyBy(fn ($p) => (string) ($p['id'] ?? ''));
        $boatPrices = collect($input['boat_prices'] ?? [])->keyBy(fn ($p) => (string) ($p['id'] ?? ''));
        $guidingPrices = collect($input['guiding_prices'] ?? [])->keyBy(fn ($p) => (string) ($p['id'] ?? ''));

        $accommodation_items = $accommodations->map(function ($acc) use ($accPrices) {
            $p = $accPrices->get((string) $acc->id);
            $unitPrice = $p ? (float) ($p['price'] ?? 0) : 0;
            $qty = $p && isset($p['qty']) ? (float) $p['qty'] : 1;
            $days = $p && isset($p['days']) ? (float) $p['days'] : 1;
            $qty = $qty > 0 ? $qty : 1;
            $days = $days > 0 ? $days : 1;
            $total = $unitPrice * $qty * $days;
            return [
                'model' => $acc,
                'price' => $unitPrice,
                'qty' => $qty,
                'days' => $days,
                'total' => $total,
                'title' => $p['title'] ?? $acc->title,
            ];
        })->values()->all();
        $boat_items = $boats->map(function ($boat) use ($boatPrices) {
            $p = $boatPrices->get((string) $boat->id);
            $unitPrice = $p ? (float) ($p['price'] ?? 0) : 0;
            $qty = $p && isset($p['qty']) ? (float) $p['qty'] : 1;
            $days = $p && isset($p['days']) ? (float) $p['days'] : 1;
            $qty = $qty > 0 ? $qty : 1;
            $days = $days > 0 ? $days : 1;
            $total = $unitPrice * $qty * $days;
            return [
                'model' => $boat,
                'price' => $unitPrice,
                'qty' => $qty,
                'days' => $days,
                'total' => $total,
                'title' => $p['title'] ?? $boat->title,
            ];
        })->values()->all();
        $guiding_items = $guidings->map(function ($g) use ($guidingPrices) {
            $p = $guidingPrices->get((string) $g->id);
            $unitPrice = $p ? (float) ($p['price'] ?? 0) : 0;
            $qty = $p && isset($p['qty']) ? (float) $p['qty'] : 1;
            $days = $p && isset($p['days']) ? (float) $p['days'] : 1;
            $qty = $qty > 0 ? $qty : 1;
            $days = $days > 0 ? $days : 1;
            $total = $unitPrice * $qty * $days;
            return [
                'model' => $g,
                'price' => $unitPrice,
                'qty' => $qty,
                'days' => $days,
                'total' => $total,
                'title' => $p['title'] ?? $g->title,
            ];
        })->values()->all();

        $componentTotal = collect($accommodation_items)->pluck('total')->sum()
            + collect($boat_items)->pluck('total')->sum()
            + collect($guiding_items)->pluck('total')->sum();

        $dateFrom = $input['date_from'] ?? '';
        $dateTo = $input['date_to'] ?? '';

        $campDescriptionShort = '';
        $campLocation = '';
        if ($camp) {
            $campDescriptionShort = Str::limit(strip_tags((string) ($camp->description_camp ?? '')), 160);
            $parts = array_filter([$camp->city ?? '', $camp->region ?? '', $camp->country ?? '']);
            $campLocation = implode(', ', $parts);
        }

        return [
            'camp' => $camp,
            'camp_description_short' => $campDescriptionShort,
            'camp_location' => $campLocation,
            'accommodations' => $accommodations,
            'boats' => $boats,
            'guidings' => $guidings,
            'accommodation_items' => $accommodation_items,
            'boat_items' => $boat_items,
            'guiding_items' => $guiding_items,
            'component_total' => $componentTotal,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'date_from_formatted' => $this->formatDateForEmail($dateFrom),
            'date_to_formatted' => $this->formatDateForEmail($dateTo),
            'number_of_persons' => $input['number_of_persons'] ?? '',
            'price' => $input['price'] ?? '',
            'additional_info' => $input['additional_info'] ?? '',
        ];
    }

    private function parseIds(mixed $value): array
    {
        if ($value === null || $value === '') {
            return [];
        }
        $ids = is_array($value) ? $value : (is_string($value) ? explode(',', $value) : []);
        $ids = array_map('intval', array_filter($ids));
        return array_values(array_filter($ids));
    }

    /**
     * Format Y-m-d date string for display in email (e.g. "15 Mar 2026" - shorter format).
     */
    private function formatDateForEmail(?string $date): string
    {
        if (empty($date)) {
            return '';
        }
        try {
            $parsed = Carbon::parse($date);
            return $parsed->format('j M Y');
        } catch (\Throwable $e) {
            return $date;
        }
    }

    /**
     * Normalize request data for preview (supports single offer or offers[] array).
     */
    private function normalizePreviewPayload(array $input): array
    {
        $customerName = '';
        if (!empty($input['recipient_type']) && $input['recipient_type'] === 'customer' && !empty($input['customer_id'])) {
            $c = User::find($input['customer_id']);
            $customerName = $c ? trim($c->firstname . ' ' . $c->lastname) : '';
        } elseif (!empty($input['manual_name'])) {
            $customerName = $input['manual_name'];
        } else {
            $customerName = $input['manual_email'] ?? 'Customer';
        }

        $offersInput = $input['offers'] ?? null;
        if (is_array($offersInput) && count($offersInput) > 0) {
            $offers = [];
            foreach ($offersInput as $row) {
                $offers[] = $this->buildSingleOfferData($row);
            }
        } else {
            $offers = [$this->buildSingleOfferData([
                'camp_id' => $input['camp_id'] ?? null,
                'accommodation_ids' => $input['accommodation_ids'] ?? '',
                'boat_ids' => $input['boat_ids'] ?? '',
                'guiding_ids' => $input['guiding_ids'] ?? '',
                'accommodation_prices' => $input['accommodation_prices'] ?? [],
                'boat_prices' => $input['boat_prices'] ?? [],
                'guiding_prices' => $input['guiding_prices'] ?? [],
                'date_from' => $input['date_from'] ?? '',
                'date_to' => $input['date_to'] ?? '',
                'number_of_persons' => $input['number_of_persons'] ?? '',
                'price' => $input['price'] ?? '',
                'additional_info' => $input['additional_info'] ?? '',
            ])];
        }

        return [
            'recipient_name' => $customerName ?: 'Customer',
            'introduction_text' => $input['introduction_text'] ?? '',
            'free_text' => $input['free_text'] ?? '',
            'offers' => $offers,
        ];
    }

    /**
     * Get base price from accommodation per_person_pricing.
     */
    private function getAccommodationBasePrice(Accommodation $accommodation): float
    {
        $perPersonPricing = $accommodation->per_person_pricing;
        
        if (is_string($perPersonPricing)) {
            $perPersonPricing = json_decode($perPersonPricing, true);
        }
        
        if (is_array($perPersonPricing) && !empty($perPersonPricing)) {
            $prices = [];
            foreach ($perPersonPricing as $tier) {
                if (!is_array($tier)) {
                    continue;
                }
                if (isset($tier['price_per_night']) && $tier['price_per_night'] > 0) {
                    $prices[] = (float) $tier['price_per_night'];
                }
                if (isset($tier['price_per_week']) && $tier['price_per_week'] > 0) {
                    $prices[] = (float) $tier['price_per_week'];
                }
            }
            if (!empty($prices)) {
                return min($prices);
            }
        }
        
        return 0;
    }

    /**
     * Get base price from rental boat prices.
     */
    private function getRentalBoatBasePrice(RentalBoat $boat): float
    {
        $prices = $boat->prices;
        
        if (is_string($prices)) {
            $prices = json_decode($prices, true);
        }
        
        if (is_array($prices) && !empty($prices)) {
            $priceValues = [];
            // Handle indexed array format [0 => ['amount' => ...]]
            if (isset($prices[0]) && is_array($prices[0])) {
                foreach ($prices as $price) {
                    if (isset($price['amount']) && $price['amount'] > 0) {
                        $priceValues[] = (float) $price['amount'];
                    }
                }
            } 
            // Handle associative array format ['per_day' => amount, ...]
            else {
                foreach ($prices as $key => $value) {
                    if (is_array($value) && isset($value['amount'])) {
                        if ($value['amount'] > 0) {
                            $priceValues[] = (float) $value['amount'];
                        }
                    } elseif (is_numeric($value) && $value > 0) {
                        $priceValues[] = (float) $value;
                    }
                }
            }
            if (!empty($priceValues)) {
                return min($priceValues);
            }
        }
        
        return 0;
    }

    /**
     * Get base price from guiding.
     */
    private function getGuidingBasePrice(Guiding $guiding): float
    {
        // If price_type is per_person, get lowest price from prices array
        if ($guiding->price_type === 'per_person') {
            $prices = $guiding->prices;
            if (is_string($prices)) {
                $prices = json_decode($prices, true);
            }
            if (is_array($prices) && !empty($prices)) {
                $priceValues = [];
                foreach ($prices as $price) {
                    if (isset($price['amount']) && $price['amount'] > 0) {
                        $priceValues[] = (float) $price['amount'];
                    }
                }
                if (!empty($priceValues)) {
                    return min($priceValues);
                }
            }
        }
        
        // Otherwise use direct price field
        return (float) ($guiding->price ?? 0);
    }

    /**
     * Return accommodations, boats, and guidings: camp-connected first (with connected: true), then all others (connected: false), sorted by title.
     */
    public function campOptions(Camp $camp)
    {
        try {
            $connectedAccIds = $camp->accommodations()
                ->where('accommodations.status', 'active')
                ->pluck('accommodations.id')
                ->all();
            $connectedBoatIds = $camp->rentalBoats()
                ->where('rental_boats.status', 'active')
                ->pluck('rental_boats.id')
                ->all();
            $connectedGuidingIds = $camp->guidings()
                ->where('guidings.status', 1)
                ->pluck('guidings.id')
                ->all();

        $allAcc = Accommodation::where('status', 'active')->orderBy('title')->get(['id', 'title', 'per_person_pricing', 'max_occupancy']);
        $allBoats = RentalBoat::where('status', 'active')->orderBy('title')->get(['id', 'title', 'prices', 'max_persons']);
        $allGuidings = Guiding::where('status', 1)->orderBy('title')->get(['id', 'title', 'price', 'prices', 'price_type', 'max_guests']);

            $campTitle = $camp->title;
            $accommodations = $this->mergeConnectedFirst($allAcc, $connectedAccIds, 'title', $campTitle);
            $boats = $this->mergeConnectedFirst($allBoats, $connectedBoatIds, 'title', $campTitle);
            $guidings = $this->mergeConnectedFirst($allGuidings, $connectedGuidingIds, 'title', $campTitle);

            // Add price and capacity data
            $accommodationsWithData = array_map(function ($item) use ($allAcc) {
                $acc = $allAcc->firstWhere('id', $item['id']);
                if ($acc) {
                    $item['price'] = $this->getAccommodationBasePrice($acc);
                    $item['capacity'] = $acc->max_occupancy ?? 0;
                }
                return $item;
            }, $accommodations);

            $boatsWithData = array_map(function ($item) use ($allBoats) {
                $boat = $allBoats->firstWhere('id', $item['id']);
                if ($boat) {
                    $item['price'] = $this->getRentalBoatBasePrice($boat);
                    $item['capacity'] = $boat->max_persons ?? 0;
                }
                return $item;
            }, $boats);

            $guidingsWithData = array_map(function ($item) use ($allGuidings) {
                $guiding = $allGuidings->firstWhere('id', $item['id']);
                if ($guiding) {
                    $item['price'] = $this->getGuidingBasePrice($guiding);
                    $item['capacity'] = $guiding->max_guests ?? 0;
                }
                return $item;
            }, $guidings);

            return response()->json([
                'accommodations' => $accommodationsWithData,
                'boats' => $boatsWithData,
                'guidings' => $guidingsWithData,
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('OfferSendoutController::campOptions failed', [
                'camp_id' => $camp->id,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'accommodations' => [],
                'boats' => [],
                'guidings' => [],
            ]);
        }
    }

    /**
     * @param \Illuminate\Support\Collection $items
     * @param int[] $connectedIds
     * @param string $titleKey
     * @param string $campName  Name of the selected camp (shown on connected items)
     * @return array<int, array{id: int, value: string, connected: bool, camp_name: string}>
     */
    private function mergeConnectedFirst($items, array $connectedIds, string $titleKey = 'title', string $campName = ''): array
    {
        $connected = [];
        $others = [];
        foreach ($items as $item) {
            $isConnected = in_array($item->id, $connectedIds, true);
            $entry = [
                'id'        => $item->id,
                'value'     => $item->{$titleKey},
                'connected' => $isConnected,
                'camp_name' => $isConnected ? $campName : '',
            ];
            if ($isConnected) {
                $connected[] = $entry;
            } else {
                $others[] = $entry;
            }
        }
        return array_merge($connected, $others);
    }

    /**
     * Save the offer as a custom camp offer for future retrieval.
     */
    private function saveCustomCampOffer(array $validated, string $recipientEmail, ?string $recipientName, string $locale): void
    {
        $offersInput = $validated['offers'] ?? null;
        $hasMultipleOffers = is_array($offersInput) && count($offersInput) > 0;

        // Prepare offers array and determine top-level values
        $offersArray = null;
        $campIds = [];
        $accommodationIds = $this->parseIds($validated['accommodation_ids'] ?? '');
        $boatIds = $this->parseIds($validated['boat_ids'] ?? '');
        $guidingIds = $this->parseIds($validated['guiding_ids'] ?? '');
        $dateFrom = $validated['date_from'] ?? null;
        $dateTo = $validated['date_to'] ?? null;
        $numberOfPersons = $validated['number_of_persons'] ?? null;
        $price = $validated['price'] ?? null;
        $additionalInfo = $validated['additional_info'] ?? null;

        if ($hasMultipleOffers) {
            $offersArray = [];
            foreach ($offersInput as $index => $offer) {
                $campId = !empty($offer['camp_id']) ? (int) $offer['camp_id'] : null;
                if ($campId) {
                    $campIds[] = $campId;
                }
                $accIds = $this->parseIds($offer['accommodation_ids'] ?? '');
                $bIds = $this->parseIds($offer['boat_ids'] ?? '');
                $gIds = $this->parseIds($offer['guiding_ids'] ?? '');
                $offersArray[] = [
                    'camp_id' => $campId,
                    'accommodation_ids' => $accIds,
                    'boat_ids' => $bIds,
                    'guiding_ids' => $gIds,
                    'accommodation_prices' => $offer['accommodation_prices'] ?? [],
                    'boat_prices' => $offer['boat_prices'] ?? [],
                    'guiding_prices' => $offer['guiding_prices'] ?? [],
                    'date_from' => $offer['date_from'] ?? '',
                    'date_to' => $offer['date_to'] ?? '',
                    'number_of_persons' => $offer['number_of_persons'] ?? '',
                    'price' => $offer['price'] ?? '',
                    'additional_info' => $offer['additional_info'] ?? '',
                ];
                // Use first offer for top-level columns
                if ($index === 0) {
                    $accommodationIds = $accIds;
                    $boatIds = $bIds;
                    $guidingIds = $gIds;
                    $dateFrom = $offer['date_from'] ?? null;
                    $dateTo = $offer['date_to'] ?? null;
                    $numberOfPersons = $offer['number_of_persons'] ?? null;
                    $price = $offer['price'] ?? null;
                    $additionalInfo = $offer['additional_info'] ?? null;
                }
            }
        } else {
            $campId = $validated['camp_id'] ?? null;
            if ($campId) {
                $campIds = [(int) $campId];
            }
            // Build single-offer array for consistency
            $offersArray = [[
                'camp_id' => $campId ? (int) $campId : null,
                'accommodation_ids' => $accommodationIds,
                'boat_ids' => $boatIds,
                'guiding_ids' => $guidingIds,
                'accommodation_prices' => $validated['accommodation_prices'] ?? [],
                'boat_prices' => $validated['boat_prices'] ?? [],
                'guiding_prices' => $validated['guiding_prices'] ?? [],
                'date_from' => $dateFrom ?? '',
                'date_to' => $dateTo ?? '',
                'number_of_persons' => $numberOfPersons ?? '',
                'price' => $price ?? '',
                'additional_info' => $additionalInfo ?? '',
            ]];
        }

        $customerName = trim($recipientName ?? $recipientEmail ?? 'Unknown');
        $name = $customerName . ' - ' . now()->format('Y-m-d');

        CustomCampOffer::create([
            'name' => $name,
            'status' => CustomCampOffer::STATUS_SENT,
            'recipient_type' => $validated['recipient_type'],
            'customer_id' => $validated['recipient_type'] === 'customer' ? ($validated['customer_id'] ?? null) : null,
            'recipient_email' => $recipientEmail,
            'recipient_name' => $recipientName,
            'recipient_phone' => $validated['manual_phone'] ?? null,
            'camp_ids' => array_values(array_unique($campIds)),
            'accommodation_ids' => $accommodationIds,
            'boat_ids' => $boatIds,
            'guiding_ids' => $guidingIds,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'number_of_persons' => $numberOfPersons,
            'price' => $price,
            'additional_info' => $additionalInfo,
            'free_text' => $validated['free_text'] ?? null,
            'offers' => $offersArray,
            'locale' => $locale,
            'created_by' => auth()->id(),
            'sent_at' => now(),
        ]);
    }
}
