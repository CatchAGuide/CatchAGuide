<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'question' => ['required', 'string'],
            'answer' => ['required', 'string'],
            'page' => ['required', 'string'],
            'language' => ['required', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
