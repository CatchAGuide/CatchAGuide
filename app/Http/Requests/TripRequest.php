<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TripRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isDraft = $this->input('is_draft') == '1';

        return $isDraft ? $this->getDraftRules() : $this->getFullRules();
    }

    private function baseRules(): array
    {
        return [
            'title'        => 'nullable|string|max:255',
            'location'     => 'nullable|string|max:255',
            'latitude'     => 'nullable|numeric|between:-90,90',
            'longitude'    => 'nullable|numeric|between:-180,180',
            'country'      => 'nullable|string|max:255',
            'city'         => 'nullable|string|max:255',
            'region'       => 'nullable|string|max:255',
            'status'       => 'nullable|string|in:active,inactive,draft',
            'user_id'      => 'nullable|integer',
            'price_per_person' => 'nullable|numeric|min:0',
            'price_single_room_addition' => 'nullable|numeric|min:0',
        ];
    }

    private function getDraftRules(): array
    {
        return $this->baseRules();
    }

    private function getFullRules(): array
    {
        $rules = $this->baseRules();

        $rules['title'] = 'required|string|max:255';
        $rules['location'] = 'required|string|max:255';
        $rules['gallery_images'] = 'nullable|array|min:1';
        $rules['duration_nights'] = 'nullable|integer|min:0';
        $rules['duration_days'] = 'nullable|integer|min:0';
        $rules['group_size_max'] = 'nullable|integer|min:1';

        return $rules;
    }
}

