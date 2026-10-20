<?php

namespace App\Http\Requests\Admin;

use App\Models\CampVacationBooking;
use App\Models\TripBooking;
use App\Services\Vacation\VacationReviewService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Admin › Reviews › Add camp/trip review. Either a booking (from "Open for review") or a
 * listing ("camp:12" / "trip:5") identifies what is reviewed.
 */
class StoreManualVacationReviewRequest extends FormRequest
{
    private CampVacationBooking|TripBooking|null $resolvedBooking = null;

    protected function prepareForValidation(): void
    {
        $this->merge([
            'guest_name' => trim((string) $this->input('guest_name')),
            'comment' => trim((string) $this->input('comment')),
        ]);
    }

    public function rules(): array
    {
        $score = ['required', 'integer', 'between:1,10'];
        $types = [VacationReviewService::TYPE_CAMP, VacationReviewService::TYPE_TRIP];

        return [
            'booking_type' => ['nullable', Rule::in($types), 'required_with:booking_id'],
            'booking_id' => ['nullable', 'integer', 'required_with:booking_type'],
            'listing' => ['required_without:booking_id', 'nullable', 'string', 'regex:/^(camp|trip):\d+$/'],
            'guest_name' => ['required', 'string', 'max:120'],
            'reviewed_at' => ['required', 'date', 'before_or_equal:today'],
            'language' => ['required', Rule::in(['en', 'de'])],
            'overall_score' => $score,
            'accommodation_score' => $score,
            'fishing_score' => $score,
            'value_score' => $score,
            'comment' => ['required', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<int, callable>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $reviews = app(VacationReviewService::class);

                if ($this->filled('booking_id')) {
                    $booking = $this->booking();
                    if ($booking === null) {
                        $validator->errors()->add('booking_id', __('admin.vacation_reviews.error_booking_missing'));
                    } elseif ($booking->review !== null) {
                        $validator->errors()->add('booking_id', __('admin.vacation_reviews.error_booking_reviewed'));
                    }

                    return;
                }

                [$type, $id] = $this->listing();
                if (! $reviews->listingExists($type, $id)) {
                    $validator->errors()->add('listing', __('admin.vacation_reviews.error_listing_missing'));
                }
            },
        ];
    }

    public function booking(): CampVacationBooking|TripBooking|null
    {
        if (! $this->filled('booking_id')) {
            return null;
        }

        return $this->resolvedBooking ??= app(VacationReviewService::class)
            ->findBooking((string) $this->input('booking_type'), (int) $this->input('booking_id'));
    }

    /**
     * @return array{0: string, 1: int}  listing type and id from "camp:12" / "trip:5"
     */
    public function listing(): array
    {
        [$type, $id] = array_pad(explode(':', (string) $this->input('listing'), 2), 2, '0');

        return [$type, (int) $id];
    }

    public function attributes(): array
    {
        return [
            'listing' => __('admin.vacation_reviews.field_listing'),
            'guest_name' => __('admin.vacation_reviews.field_guest_name'),
            'reviewed_at' => __('admin.vacation_reviews.field_reviewed_at'),
            'language' => __('admin.vacation_reviews.field_language'),
            'overall_score' => __('admin.vacation_reviews.score_overall'),
            'accommodation_score' => __('admin.vacation_reviews.score_accommodation'),
            'fishing_score' => __('admin.vacation_reviews.score_fishing'),
            'value_score' => __('admin.vacation_reviews.score_value'),
            'comment' => __('admin.vacation_reviews.th_comment'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
