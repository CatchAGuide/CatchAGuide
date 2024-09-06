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

    protected function prepareForValidation()
    {
        $this->merge([
            'answer' => mb_convert_encoding($this->input('answer'), 'UTF-8', 'auto'),
            'question' => mb_convert_encoding($this->input('question'), 'UTF-8', 'auto'),
        ]);
    }
}
