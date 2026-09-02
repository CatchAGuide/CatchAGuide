<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacationTestimonialRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_published' => $this->boolean('is_published'),
        ]);
    }

    public function rules(): array
    {
        return [
            'quote' => ['required', 'string', 'max:2000'],
            'author' => ['required', 'string', 'max:255'],
            'rating' => ['required', 'numeric', 'min:0', 'max:10'],
            'reviewed_on' => ['nullable', 'date'],
            'listing_title' => ['nullable', 'string', 'max:255'],
            'listing_url' => ['nullable', 'string', 'max:2048'],
            'is_published' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
