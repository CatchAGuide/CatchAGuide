@php
    /**
     * Reusable offers catalog multi-select (tags + searchable checkbox dropdown).
     *
     * @var string $inputName e.g. species[]
     * @var string $inputPrefix unique DOM id prefix
     * @var string $label
     * @var string $placeholder
     * @var string $searchPlaceholder
     * @var iterable|array $options list of ['value' => string, 'label' => string]
     * @var array<int, string|int> $selectedValues
     * @var string|null $fieldClass
     * @var string|null $wrapperClass
     */
    $fieldClass = $fieldClass ?? 'vacation-filters__field';
    $wrapperClass = $wrapperClass ?? '';
    $options = collect($options ?? [])
        ->map(function ($option) {
            if (! is_array($option)) {
                return null;
            }
            $value = (string) ($option['value'] ?? $option['id'] ?? '');
            if ($value === '') {
                return null;
            }

            return [
                'value' => $value,
                'label' => (string) ($option['label'] ?? $option['name'] ?? $value),
            ];
        })
        ->filter()
        ->values();
    $selectedValues = collect($selectedValues ?? [])
        ->map(fn ($value) => (string) $value)
        ->filter(fn ($value) => $value !== '')
        ->unique()
        ->values()
        ->all();
    $selectedOptions = $options
        ->filter(fn ($option) => in_array($option['value'], $selectedValues, true))
        ->values()
        ->all();
    $selectData = [
        'options' => $options->all(),
        'selected' => $selectedValues,
        'placeholder' => $placeholder,
        'removeLabel' => __('offers.remove_filter'),
    ];
@endphp

@if($options->isNotEmpty())
    <div class="{{ $fieldClass }} {{ $wrapperClass }}">
        <label class="form-label" id="{{ $inputPrefix }}-label">{{ $label }}</label>
        <div
            class="offers-multi-select"
            data-offers-multi-select
            data-input-name="{{ $inputName }}"
        >
            <script type="application/json" data-offers-multi-data>
                @json($selectData)
            </script>

            <button
                type="button"
                class="offers-multi-select__control"
                data-offers-multi-toggle
                aria-haspopup="listbox"
                aria-expanded="false"
                aria-labelledby="{{ $inputPrefix }}-label"
                id="{{ $inputPrefix }}-toggle"
            >
                <span class="offers-multi-select__tags" data-offers-multi-tags>
                    @forelse($selectedOptions as $option)
                        <span class="offers-multi-select__tag" data-value="{{ $option['value'] }}">
                            <span class="offers-multi-select__tag-text">{{ $option['label'] }}</span>
                            <span class="offers-multi-select__tag-remove" data-offers-multi-remove="{{ $option['value'] }}" aria-hidden="true">&times;</span>
                        </span>
                    @empty
                        <span class="offers-multi-select__placeholder">{{ $placeholder }}</span>
                    @endforelse
                </span>
                <span class="offers-multi-select__caret" aria-hidden="true"></span>
            </button>

            <div
                class="offers-multi-select__dropdown"
                data-offers-multi-dropdown
                hidden
                role="listbox"
                aria-multiselectable="true"
                aria-labelledby="{{ $inputPrefix }}-label"
            >
                <div class="offers-multi-select__search">
                    <input
                        type="search"
                        class="offers-multi-select__search-input"
                        data-offers-multi-search
                        placeholder="{{ $searchPlaceholder }}"
                        autocomplete="off"
                    >
                </div>
                <div class="offers-multi-select__list" data-offers-multi-list>
                    @foreach($options as $option)
                        @php
                            $isChecked = in_array($option['value'], $selectedValues, true);
                            $optionId = $inputPrefix.'-opt-'.$option['value'];
                        @endphp
                        <label
                            class="offers-multi-select__option{{ $isChecked ? ' is-checked' : '' }}"
                            data-offers-multi-option
                            data-value="{{ $option['value'] }}"
                            data-label="{{ $option['label'] }}"
                            for="{{ $optionId }}"
                        >
                            <input
                                type="checkbox"
                                class="offers-multi-select__checkbox"
                                id="{{ $optionId }}"
                                value="{{ $option['value'] }}"
                                @checked($isChecked)
                                data-offers-multi-checkbox
                            >
                            <span class="offers-multi-select__option-label">{{ $option['label'] }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="offers-multi-select__inputs" data-offers-multi-inputs>
                @foreach($selectedOptions as $option)
                    <input type="hidden" name="{{ $inputName }}" value="{{ $option['value'] }}">
                @endforeach
            </div>
        </div>
    </div>
@endif
