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
        $cards = collect($this->input('cards', []))
            ->take(MonthlyHighlight::MAX_ITEMS)
            ->map(fn ($card) => [
                'country_id' => filled($card['country_id'] ?? null) ? (int) $card['country_id'] : null,
                'target_id' => filled($card['target_id'] ?? null) ? (int) $card['target_id'] : null,
            ])
            ->values()
            ->all();

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'cards' => $cards,
            'items' => MonthlyHighlight::itemsFromCardInput($cards),
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
            'cards' => ['nullable', 'array', 'max:'.MonthlyHighlight::MAX_ITEMS],
            'cards.*.country_id' => ['nullable', 'integer', Rule::exists('category_entities', 'id')->where('type', 'country')],
            'cards.*.target_id' => [
                'nullable',
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
            foreach ($this->input('cards', []) as $index => $card) {
                $hasCountry = filled($card['country_id'] ?? null);
                $hasTarget = filled($card['target_id'] ?? null);

                if ($hasCountry xor $hasTarget) {
                    $validator->errors()->add(
                        "cards.{$index}",
                        'Each card needs both a country and a target fish.'
                    );
                }
            }
        });
    }
}
