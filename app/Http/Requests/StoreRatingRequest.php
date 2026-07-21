<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRatingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('comment')) {
            $this->merge([
                'comment' => trim((string) $this->input('comment')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'rating_overall' => ['required'],
            'rating_guide' => ['required'],
            'rating_region' => ['required'],
            'comment' => ['required', 'string', 'min:1']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
