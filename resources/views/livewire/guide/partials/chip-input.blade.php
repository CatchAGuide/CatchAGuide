{{--
    Chip picker bound to a Livewire array property.
    @param string $model        Livewire property name (array of values)
    @param array  $options      value => label (suggestions)
    @param bool   $allowCustom  typed values not in $options are kept as written
    @param string $id, $label, $placeholder, $help
    @param bool   $required
    @param string $variant      'dark' | 'soft'
    @param bool   $addButton    show an "Add" button next to the input
--}}
@php
    $variant = $variant ?? 'dark';
    $allowCustom = $allowCustom ?? true;
    $addButton = $addButton ?? false;
@endphp
<div class="ga-field"
     x-data="{
        values: $wire.entangle('{{ $model }}'),
        options: @js((object) $options),
        query: '',
        open: false,
        label(v) { return this.options[v] ?? v },
        matches() {
            const q = this.query.trim().toLowerCase();
            return Object.entries(this.options)
                .filter(([k, l]) => !this.values.includes(k) && (q === '' || l.toLowerCase().includes(q)))
                .slice(0, 8);
        },
        add(v) {
            if (v && !this.values.includes(v)) this.values.push(v);
            this.query = '';
        },
        addTyped() {
            const q = this.query.trim();
            if (q === '') return;
            const hit = Object.entries(this.options).find(([k, l]) => l.toLowerCase() === q.toLowerCase());
            if (hit) { this.add(hit[0]); return; }
            if ({{ $allowCustom ? 'true' : 'false' }}) this.add(q.slice(0, 40));
        },
        remove(i) { this.values.splice(i, 1) },
     }"
     @click.outside="open = false">
    <label class="ga-label" for="{{ $id }}">{{ $label }}@if ($required ?? false) <span class="ga-required" aria-hidden="true">*</span>@endif</label>

    <ul class="ga-chips" x-show="values.length" role="list">
        <template x-for="(v, i) in values" :key="v">
            <li class="ga-chip ga-chip--{{ $variant }}">
                <span x-text="label(v)"></span>
                <button type="button" class="ga-chip__remove" @click="remove(i)" :aria-label="'{{ __('guide_area.common.remove') }} ' + label(v)">&times;</button>
            </li>
        </template>
    </ul>

    <div class="ga-combo">
        <div class="ga-combo__row">
            <input type="text" id="{{ $id }}" class="ga-input" autocomplete="off"
                   placeholder="{{ $placeholder }}"
                   x-model="query" @focus="open = true" @input="open = true"
                   @keydown.enter.prevent="addTyped()" @keydown.escape="open = false"
                   role="combobox" :aria-expanded="open.toString()" aria-autocomplete="list" aria-controls="{{ $id }}-list">
            @if ($addButton)
                <button type="button" class="ga-btn ga-btn--dark" @click="addTyped()">{{ __('guide_area.common.add') }}</button>
            @endif
        </div>
        <ul id="{{ $id }}-list" class="ga-combo__list" role="listbox" x-show="open && matches().length" x-cloak>
            <template x-for="[k, l] in matches()" :key="k">
                <li role="option" class="ga-combo__option" @mousedown.prevent="add(k)" x-text="l"></li>
            </template>
        </ul>
    </div>

    @if (! empty($help))
        <p class="ga-help">{{ $help }}</p>
    @endif
    @error($model) <p class="ga-error">{{ $message }}</p> @enderror
    @error($model.'.*') <p class="ga-error">{{ $message }}</p> @enderror
</div>
