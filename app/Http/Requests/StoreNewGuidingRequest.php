<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreNewGuidingRequest extends FormRequest
{
    public function rules(): array
    {
        Log::info($this->all());
        return [
            'title' => 'required|string|max:255',
            'title_image' => 'required|array',
            'title_image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'primaryImage' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'type_of_fishing' => 'required|string|in:shore,boat',
            'boat_type' => 'required_if:type_of_fishing,boat|string',
            'target_fish' => 'required|string',
            'methods' => 'required|string',
            'water_types' => 'required|string',
            'experience_level' => 'nullable|string',
            'inclusions' => 'nullable|array',
            'style_of_fishing' => 'required|string',
            'desc_course_of_action' => 'required|string',
            'desc_meeting_point' => 'required|string',
            'desc_tour_unique' => 'required|string',
            'desc_starting_time' => 'required|string',
            'tour_type' => 'required|string',
            'duration' => 'required|string',
            'duration_hours' => 'required_if:duration,half_day,full_day|nullable|integer|min:1|max:24',
            'duration_days' => 'required_if:duration,multi_day|nullable|integer|min:1|max:365',
            'no_guest' => 'required|integer|min:1',
            'price_type' => 'required|in:per_person,per_boat',
            'price_per_person_*' => 'required_if:price_type,per_person|numeric|min:0',
            'price_per_boat' => 'required_if:price_type,per_boat|numeric|min:0',
            'extras' => 'nullable|string',
            'allowed_booking_advance' => 'required|string',
            'booking_window' => 'required|string',
            'seasonal_trip' => 'required|string',
            'months' => 'required_if:seasonal_trip,season_monthly|nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'title' => mb_convert_encoding($this->input('title'), 'UTF-8', 'auto'),
            'location' => mb_convert_encoding($this->input('location'), 'UTF-8', 'auto'),
            'boat_type' => mb_convert_encoding($this->input('boat_type'), 'UTF-8', 'auto'),
            'course_of_action' => mb_convert_encoding($this->input('desc_course_of_action'), 'UTF-8', 'auto'),
            'meeting_point' => mb_convert_encoding($this->input('desc_meeting_point'), 'UTF-8', 'auto'),
            'tour_unique' => mb_convert_encoding($this->input('desc_tour_unique'), 'UTF-8', 'auto'),
        ]);
    }
}