<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Foundation\Http\FormRequest;

class StoreThreadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'body' => ['required'],
            'excerpt' => ['string','nullable'],
            'category_id' => ['required', 'integer', 'exists:categories,id']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
