<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVacationRatingRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('comment')) {
            $this->merge([
                'comment' => trim((string) $this->input('comment')),
            ]);
        }
    }

    public function rules(): array
    {
        $score = ['required', 'integer', 'between:1,10'];

        return [
            'rating_overall' => $score,
            'rating_accommodation' => $score,
            'rating_fishing' => $score,
            'rating_value' => $score,
            'comment' => ['required', 'string', 'min:1', 'max:5000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'rating_overall' => __('vacations.rating_overall'),
            'rating_accommodation' => __('vacations.rating_accommodation'),
            'rating_fishing' => __('vacations.rating_fishing'),
            'rating_value' => __('vacations.rating_value'),
            'comment' => __('guidings.rating_comment'),
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
