<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating' => ['required'],
            'description' => ['nullable']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
