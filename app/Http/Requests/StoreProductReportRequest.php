<?php

namespace App\Http\Requests;

use App\Models\ProductReport;
use App\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];

        foreach (['name', 'email', 'phone', 'description', 'reported_url'] as $field) {
            if ($this->has($field)) {
                $merge[$field] = trim((string) $this->input($field));
            }
        }

        if ($this->filled('source_id') && !is_numeric($this->input('source_id'))) {
            $merge['source_id'] = null;
        }

        if ($this->has('source_type') && $this->input('source_type') === '') {
            $merge['source_type'] = null;
        }

        if ($merge !== []) {
            $this->merge($merge);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'reason' => ['required', 'string', Rule::in(ProductReport::reasonKeys())],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'reported_url' => ['required', 'string', 'max:2048'],
            'source_type' => ['nullable', 'string', Rule::in([
                ProductReport::SOURCE_GUIDING,
                ProductReport::SOURCE_TRIP,
                ProductReport::SOURCE_CAMP,
            ])],
            'source_id' => ['nullable', 'integer', 'min:1'],
            'g-recaptcha-response' => Recaptcha::production(),
        ];
    }
}
