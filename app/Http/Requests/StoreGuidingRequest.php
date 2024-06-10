<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuidingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'location' => ['required', 'string'],
            'water_sonstiges' => ['string', 'nullable'],
            'methods_sonstiges' => ['string', 'nullable'],
            'recommended_for_anfaenger' => ['boolean'],
            'recommended_for_fortgeschrittene' => ['boolean'],
            'recommended_for_profis' => ['boolean'],
            'max_guests' => ['required', 'integer'],
            'duration' => ['required', 'numeric'],
            'required_special_license' => ['nullable', 'string'],
            'fishing_type' => ['required', 'string'],
            'fishing_from' => ['required', 'string'],
            'description' => ['nullable', 'string'],
            'required_equipment' => ['nullable', 'string'],
            'provided_equipment' => ['nullable', 'string'],
            'additional_information' => ['nullable', 'string'],
            'price' => ['required', 'numeric'],
            'price_two_persons' => ['nullable', 'numeric'],
            'price_three_persons' => ['nullable', 'numeric'],
            'price_four_persons' => ['nullable', 'numeric'],
            'price_five_persons' => ['nullable', 'numeric'],
            'lat' => ['nullable', 'numeric'],
            'lng' => ['nullable', 'numeric'],
            'rest_method' => ['nullable', 'string'],
            'water_name' => ['nullable', 'string'],
          //  'thumbnail' => ['file'],
            'catering' => ['nullable', 'string'],
            'needed_equipment' => ['nullable', 'string'],
            'meeting_point' => ['nullable', 'string'],
            'target_fish_sonstiges' => ['nullable', 'string'],
            'image_name' => ['required|file|mimes:png,jpg,jpeg|max:2048'],
            'avatar' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
