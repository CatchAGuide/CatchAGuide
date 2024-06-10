<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBlockedEventRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'start' => ['required'],
            'end' => ['required']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
