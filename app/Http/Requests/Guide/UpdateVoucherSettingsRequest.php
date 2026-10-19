<?php

namespace App\Http\Requests\Guide;

use App\Enums\VoucherDesign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVoucherSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->canViewGuideTools();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'voucher_design' => ['required', Rule::enum(VoucherDesign::class)],
            'voucher_enabled' => ['sometimes', 'array'],
            'voucher_enabled.*' => ['integer'],
        ];
    }

    public function design(): VoucherDesign
    {
        return VoucherDesign::from((string) $this->validated('voucher_design'));
    }

    /**
     * Ticked tour ids. Ids of other guides' tours are ignored by the service's user_id scope.
     *
     * @return list<int>
     */
    public function enabledGuidingIds(): array
    {
        return array_values(array_unique(array_map('intval', (array) $this->validated('voucher_enabled', []))));
    }
}
