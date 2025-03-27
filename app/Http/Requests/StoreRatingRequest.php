<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'rating_overall' => ['required'],
            'rating_guide' => ['required'],
            'rating_region' => ['required'],
            'comment' => ['nullable']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
