<?php

namespace App\Http\Requests\Admin;

use App\Models\MonthlyHighlight;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateMonthlyHighlightRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $countryIds = collect($this->input('country_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $targetIds = collect($this->input('target_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $items = $countryIds
            ->map(fn (int $id) => ['type' => MonthlyHighlight::ITEM_TYPE_COUNTRY, 'id' => $id])
            ->concat(
                $targetIds->map(fn (int $id) => ['type' => MonthlyHighlight::ITEM_TYPE_TARGET, 'id' => $id])
            )
            ->values()
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'country_ids' => $countryIds->all(),
            'target_ids' => $targetIds->all(),
            'items' => $items,
        ]);
    }

    public function rules(): array
    {
        $highlight = $this->route('monthly_highlight');

        return [
            'month' => [
                'required',
                'integer',
                'between:1,12',
                Rule::unique('monthly_highlights', 'month')->ignore($highlight?->id),
            ],
            'title_en' => ['required', 'string', 'max:255'],
            'title_de' => ['required', 'string', 'max:255'],
            'subtitle_en' => ['nullable', 'string', 'max:1000'],
            'subtitle_de' => ['nullable', 'string', 'max:1000'],
            'country_ids' => ['nullable', 'array'],
            'country_ids.*' => ['integer', Rule::exists('c_countries', 'id')],
            'target_ids' => ['nullable', 'array'],
            'target_ids.*' => [
                'integer',
                Rule::exists('category_pages', 'id')->where(fn ($q) => $q->where('type', 'Targets')),
            ],
            'items' => ['nullable', 'array', 'max:'.MonthlyHighlight::MAX_ITEMS],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $total = count($this->input('country_ids', [])) + count($this->input('target_ids', []));
            if ($total > MonthlyHighlight::MAX_ITEMS) {
                $validator->errors()->add(
                    'items',
                    'Select at most '.MonthlyHighlight::MAX_ITEMS.' countries and target fish combined.'
                );
            }
        });
    }
}
