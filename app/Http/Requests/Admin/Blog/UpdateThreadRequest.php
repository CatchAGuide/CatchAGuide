<?php

namespace App\Http\Requests\Admin\Blog;

use Illuminate\Foundation\Http\FormRequest;

class UpdateThreadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'title' => ['required', 'string'],
            'body' => ['required', 'string'],
            'excerpt' => ['string','nullable'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'author' => ['required', 'string']
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
