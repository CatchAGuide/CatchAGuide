<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;

class StoreNewGuidingRequest extends FormRequest
{
    /** @var int Max image upload size per file in kilobytes (align with PHP/HHVM post limits on production). */
    public const DRAFT_IMAGE_MAX_KB = 51200;

    public function rules(): array
    {
        $rules = [
            'is_draft' => 'sometimes|boolean',
            'title' => 'required|string|max:255',
            'title_image' => 'nullable|array',
            'title_image.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'primaryImage' => 'required|integer|min:0',
            'location' => 'required|string|max:255',
            'type_of_fishing' => 'required|string|in:shore,boat',
            'type_of_boat' => 'required_if:type_of_fishing,boat|string',
            'target_fish' => 'required|string',
            'methods' => 'required|string',
            'water_types' => 'required|string',
            'experience_level' => 'nullable|array',
            // 'inclusions' => 'nullable|array',
            'style_of_fishing' => 'required|string',
            'desc_course_of_action' => 'required_if:is_update,0|string',
            'desc_meeting_point' => 'required_if:is_update,0|string',
            'desc_tour_unique' => 'required_if:is_update,0|string',
            'desc_starting_time' => 'required_if:is_update,0|string',
            'tour_type' => 'required|string',
            'duration' => 'required|string',
            'duration_hours' => 'required_if:duration,half_day,full_day|nullable|integer|min:1|max:24',
            'duration_days' => 'required_if:duration,multi_day|nullable|integer|min:1|max:365',
            'no_guest' => 'required|integer|min:0',
            'price_type' => 'required|in:per_person,per_boat',
            'price_per_person_*' => 'required_if:price_type,per_person|numeric|min:0',
            'price_per_boat' => 'required_if:price_type,per_boat|numeric|min:0',
            'boat_extras' => 'nullable|string',
            'allowed_booking_advance' => 'required|string',
            'booking_window' => 'required|string',
            'seasonal_trip' => 'required|string',
            'months' => 'required_if:seasonal_trip,season_monthly|nullable|array',
        ];

        // If it's a draft submission, make all fields optional
        if ($this->input('is_draft') == 1) {
            foreach ($rules as $field => $rule) {
                if (is_string($rule)) {
                    if (str_contains($rule, 'required_if')) {
                        // Never replace "required_if" with "nullable" — that yields invalid rules like
                        // "nullable:price_type,per_person|numeric". Strip the conditional instead.
                        $stripped = preg_replace('/required_if:[^|]+/', '', $rule);
                        $stripped = trim((string) $stripped, '|');
                        $stripped = preg_replace('/\|{2,}/', '|', $stripped);
                        $rules[$field] = $stripped === '' ? 'nullable' : 'nullable|' . $stripped;
                    } else {
                        $rules[$field] = str_replace('required', 'nullable', $rule);
                    }
                } elseif (is_array($rule)) {
                    $rules[$field] = array_filter($rule, function ($item) {
                        return $item !== 'required';
                    });
                    array_unshift($rules[$field], 'nullable');
                }
            }

            // Draft uploads: do not use the "image" rule (fails HEIC / some mobile crops). Allow WebP.
            $rules['title_image'] = 'nullable|array';
            $rules['title_image.*'] = [
                'nullable',
                'file',
                'max:' . self::DRAFT_IMAGE_MAX_KB,
                'mimes:jpeg,jpg,jpe,png,gif,svg,webp',
            ];
        }

        // If it's an update/edit, make all fields optional (deactivate validation for editing)
        if ($this->input('is_update') == 1) {
            foreach ($rules as $field => $rule) {
                if (is_string($rule)) {
                    if (str_contains($rule, 'required_if')) {
                        $stripped = preg_replace('/required_if:[^|]+/', '', $rule);
                        $stripped = trim((string) $stripped, '|');
                        $stripped = preg_replace('/\|{2,}/', '|', $stripped);
                        $rules[$field] = $stripped === '' ? 'nullable' : 'nullable|' . $stripped;
                    } else {
                        $rules[$field] = str_replace('required', 'nullable', $rule);
                    }
                } elseif (is_array($rule)) {
                    $rules[$field] = array_filter($rule, function ($item) {
                        return $item !== 'required';
                    });
                    array_unshift($rules[$field], 'nullable');
                }
            }
        }

        return $rules;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        $trimmedData = [];

        // Get all fields that have 'string' in their validation rules
        foreach ($this->rules() as $field => $rules) {
            if (is_string($rules) && strpos($rules, 'string') !== false && $this->has($field)) {
                $trimmedData[$field] = trim(mb_convert_encoding((string) $this->input($field), 'UTF-8', 'auto'));
            }
        }

        $s = static fn (?string $v): string => trim((string) ($v ?? ''));

        // Keep the existing specific field formatting (avoid trim(null) on PHP 8+ when fields are absent)
        $this->merge([
            'title' => mb_convert_encoding($s($this->input('title')), 'UTF-8', 'auto'),
            'location' => mb_convert_encoding($s($this->input('location')), 'UTF-8', 'auto'),
            'boat_type' => mb_convert_encoding($s($this->input('boat_type')), 'UTF-8', 'auto'),
            'course_of_action' => mb_convert_encoding($s($this->input('desc_course_of_action')), 'UTF-8', 'auto'),
            'meeting_point' => mb_convert_encoding($s($this->input('desc_meeting_point')), 'UTF-8', 'auto'),
            'tour_unique' => mb_convert_encoding($s($this->input('desc_tour_unique')), 'UTF-8', 'auto'),
        ]);

        $this->merge($trimmedData);
    }

    protected function failedValidation(Validator $validator)
    {
        if ($this->boolean('is_draft')) {
            Log::warning('StoreNewGuidingRequest draft validation failed', [
                'errors' => $validator->errors()->toArray(),
            ]);
        }

        parent::failedValidation($validator);
    }
}