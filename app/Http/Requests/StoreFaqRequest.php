<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'page' => ['nullable', 'string'],
            'language' => ['nullable', 'string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
