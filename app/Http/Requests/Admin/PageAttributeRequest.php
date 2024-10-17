<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class PageAttributeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'page' => ['required', 'string'],
            'meta_type' => ['required', 'string'],
            'domain' => ['required', 'string'],
            'uri' => ['required', 'string'],
            'content' => ['required', 'string'],
        ];
    }
}
