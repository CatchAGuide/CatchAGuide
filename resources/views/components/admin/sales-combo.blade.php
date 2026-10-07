@props([
    // Inline options (camp sub-options) or the key of window.salesOptions (tour / camp / trip lists).
    'options' => null,
    'optionsKey' => null,
    'value' => null,
    // Livewire method called with ...$args and the picked id.
    'method',
    'args' => [],
    'label' => null,
])
{{--
    Searchable "#ID · Name" selection (spec §4.2): typing filters by ID and name, a bare ID
    ("1057" / "#1057") selects that product after a short pause, Enter or leaving the field;
    leaving without a valid match restores the previous selection. No prices in the options.
--}}
<div
    {{ $attributes->merge(['class' => 'sb-combo']) }}
    wire:ignore
    x-data="salesCombo({
        options: @js($options),
        optionsKey: @js($optionsKey),
        value: @js($value),
        method: @js($method),
        args: @js(array_values($args)),
    })"
    @click.outside="close()"
>
    @if($label)
        <span class="sb-label">{{ $label }}</span>
    @endif
    <input
        type="text"
        class="form-control form-control-sm sb-combo__input"
        x-model="text"
        @input="onInput()"
        @focus="open = true"
        @blur="onBlur()"
        @keydown.arrow-down.prevent="move(1)"
        @keydown.arrow-up.prevent="move(-1)"
        @keydown.enter.prevent="pickActive()"
        @keydown.escape="close(true)"
        placeholder="{{ __('sales.builder.combo_placeholder') }}"
        autocomplete="off"
        role="combobox"
        :aria-expanded="open.toString()"
    >
    <ul class="sb-combo__list" x-show="open && filtered.length" x-cloak role="listbox">
        <template x-for="(option, i) in filtered" :key="option.id">
            <li
                role="option"
                :class="{ 'is-active': i === active, 'is-selected': option.id === value }"
                @mousedown.prevent="choose(option)"
                x-text="option.label"
            ></li>
        </template>
    </ul>
</div>
