<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactformRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name'=>'required|max:25',
            'email'=>'required|email',
            'phone'=>'required|max:25',
            'message'=>'required|max:350',
            'camper_id'=>'nullable|integer|exists:campers,id'
        ];
    }

    public function authorize()
    {
        return true;
    }
}
