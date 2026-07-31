<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RejectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization for the booking token happens in the controller.
     */
    public function authorize(): bool
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
            'reason' => 'required',
            'alternative_dates' => 'required',
        ];
    }
}
