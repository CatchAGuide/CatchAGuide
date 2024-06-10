<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreCategoryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'name_en' => ['string','nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
