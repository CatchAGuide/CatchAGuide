<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTermsSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'language' => ['required', 'string', 'in:de,en'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => mb_convert_encoding((string) $this->input('title'), 'UTF-8', 'auto'),
            'content' => mb_convert_encoding((string) $this->input('content'), 'UTF-8', 'auto'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
