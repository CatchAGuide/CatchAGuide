<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNewGuidingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'title_image' => 'required|array',
            'title_image.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'location' => 'required|string|max:255',
            'type_of_fishing' => 'required|string',
            'target_fish' => 'required|array',
            'methods' => 'required|array',
            'water_types' => 'required|array',
            'experience_level' => 'required|string',
            'style_of_fishing' => 'required|string',
            'course_of_action' => 'required|string',
            'special_about' => 'required|string',
            'tour_unique' => 'required|string',
            'starting_time' => 'required|string',
            'private' => 'required|string',
            'duration' => 'required|string',
            'no_guest' => 'required|integer|min:1',
            'price' => 'required|string',
            'allowed_booking_advance' => 'required|string',
            'booking_window' => 'required|string',
            'seasonal_trip' => 'required|string',
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
            'description' => mb_convert_encoding($this->input('description'), 'UTF-8', 'auto'),
            'water_sonstiges' => mb_convert_encoding($this->input('water_sonstiges'), 'UTF-8', 'auto'),
            'methods_sonstiges' => mb_convert_encoding($this->input('methods_sonstiges'), 'UTF-8', 'auto'),
            'required_special_license' => mb_convert_encoding($this->input('required_special_license'), 'UTF-8', 'auto'),
            'water_name' => mb_convert_encoding($this->input('water_name'), 'UTF-8', 'auto'),
            'meeting_point' => mb_convert_encoding($this->input('meeting_point'), 'UTF-8', 'auto'),
            'target_fish_sonstiges' => mb_convert_encoding($this->input('target_fish_sonstiges'), 'UTF-8', 'auto'),
            'required_equipment' => mb_convert_encoding($this->input('required_equipment'), 'UTF-8', 'auto'),
            'additional_information' => mb_convert_encoding($this->input('additional_information'), 'UTF-8', 'auto'),
            'catering' => mb_convert_encoding($this->input('catering'), 'UTF-8', 'auto'),
            'needed_equipment' => mb_convert_encoding($this->input('needed_equipment'), 'UTF-8', 'auto'),
        ]);
    }
}
