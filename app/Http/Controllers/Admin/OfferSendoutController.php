<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Admin\OfferSendoutMail;
use App\Models\Accommodation;
use App\Models\Camp;
use App\Models\Guiding;
use App\Models\RentalBoat;
use App\Models\User;
use Carbon\Carbon;
use App\Models\EmailLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OfferSendoutController extends Controller
{
    /**
     * Display the offer builder / sendout page.
     */
    public function index()
    {
        $customers = User::whereNotNull('email')
            ->orderBy('firstname')
            ->get(['id', 'firstname', 'lastname', 'email', 'phone']);

        $camps = Camp::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $accommodations = Accommodation::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $boats = RentalBoat::where('status', 'active')
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        $guidings = Guiding::where('status', 1)
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return view('admin.pages.offer-sendout.index', [
            'customers' => $customers,
            'camps' => $camps,
            'accommodations' => $accommodations,
            'boats' => $boats,
            'guidings' => $guidings,
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

        try {
            Mail::to($recipientEmail)
                ->locale($locale)
                ->cc($adminEmail)
                ->send(new OfferSendoutMail($payload));
        } catch (\Throwable $e) {
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
            ]),
        ]);

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
                'date_from' => $validated['date_from'] ?? '',
                'date_to' => $validated['date_to'] ?? '',
                'number_of_persons' => $validated['number_of_persons'] ?? '',
                'price' => $validated['price'] ?? '',
                'additional_info' => $validated['additional_info'] ?? '',
            ])];
        }

        return [
            'recipient_name' => $recipientName,
            'free_text' => $validated['free_text'] ?? '',
            'offers' => $offers,
        ];
    }

    /**
     * Build one offer block for the catalog (camp highlight + details, no item images).
     */
    private function buildSingleOfferData(array $input): array
    {
        $camp = !empty($input['camp_id']) ? Camp::find($input['camp_id']) : null;
        $accommodationIds = $this->parseIds($input['accommodation_ids'] ?? '');
        $boatIds = $this->parseIds($input['boat_ids'] ?? '');
        $guidingIds = $this->parseIds($input['guiding_ids'] ?? '');

        $accommodations = $accommodationIds ? Accommodation::whereIn('id', $accommodationIds)->orderBy('title')->get() : collect();
        $boats = $boatIds ? RentalBoat::whereIn('id', $boatIds)->orderBy('title')->get() : collect();
        $guidings = $guidingIds ? Guiding::whereIn('id', $guidingIds)->orderBy('title')->get() : collect();

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
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'date_from_formatted' => $this->formatDateForEmail($dateFrom),
            'date_to_formatted' => $this->formatDateForEmail($dateTo),
            'number_of_persons' => $input['number_of_persons'] ?? '',
            'price' => $input['price'] ?? '',
            'additional_info' => $input['additional_info'] ?? '',
        ];
    }

    private function parseIds(?string $value): array
    {
        if (empty($value)) {
            return [];
        }
        $ids = is_string($value) ? array_map('intval', array_filter(explode(',', $value))) : (array) $value;
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
                'date_from' => $input['date_from'] ?? '',
                'date_to' => $input['date_to'] ?? '',
                'number_of_persons' => $input['number_of_persons'] ?? '',
                'price' => $input['price'] ?? '',
                'additional_info' => $input['additional_info'] ?? '',
            ])];
        }

        return [
            'recipient_name' => $customerName ?: 'Customer',
            'free_text' => $input['free_text'] ?? '',
            'offers' => $offers,
        ];
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

            $allAcc = Accommodation::where('status', 'active')->orderBy('title')->get(['id', 'title']);
            $allBoats = RentalBoat::where('status', 'active')->orderBy('title')->get(['id', 'title']);
            $allGuidings = Guiding::where('status', 1)->orderBy('title')->get(['id', 'title']);

            $accommodations = $this->mergeConnectedFirst($allAcc, $connectedAccIds, 'title');
            $boats = $this->mergeConnectedFirst($allBoats, $connectedBoatIds, 'title');
            $guidings = $this->mergeConnectedFirst($allGuidings, $connectedGuidingIds, 'title');

            return response()->json([
                'accommodations' => $accommodations,
                'boats' => $boats,
                'guidings' => $guidings,
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
     * @return array<int, array{id: int, value: string, connected: bool}>
     */
    private function mergeConnectedFirst($items, array $connectedIds, string $titleKey = 'title'): array
    {
        $connected = [];
        $others = [];
        foreach ($items as $item) {
            $entry = ['id' => $item->id, 'value' => $item->{$titleKey}, 'connected' => in_array($item->id, $connectedIds, true)];
            if ($entry['connected']) {
                $connected[] = $entry;
            } else {
                $others[] = $entry;
            }
        }
        return array_merge($connected, $others);
    }
}
