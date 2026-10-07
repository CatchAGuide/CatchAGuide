@extends('admin.layouts.app')

@section('title', $document?->number ?? __('sales.builder.heading'))

@section('custom_style')
<link href="{{ mix('css/admin-sales-builder.css') }}" rel="stylesheet">
@endsection

@section('content')
<div class="side-app">
    <div class="main-container container-fluid">
        <script>
            // "#ID · Name" options for the product combos, shared by every card (sent once, not per Livewire update).
            window.salesOptions = @json($options);

            document.addEventListener('alpine:init', () => {
                Alpine.data('salesCombo', (config) => ({
                    options: config.options ?? window.salesOptions?.[config.optionsKey] ?? [],
                    value: config.value,
                    text: '',
                    open: false,
                    active: 0,
                    timer: null,

                    init() {
                        this.text = this.labelFor(this.value);
                    },

                    labelFor(id) {
                        return this.options.find((option) => option.id === id)?.label ?? '';
                    },

                    get filtered() {
                        const query = this.text.trim().toLowerCase();
                        if (query === '' || query === this.labelFor(this.value).toLowerCase()) {
                            return this.options.slice(0, 60);
                        }
                        const id = query.replace(/^#/, '');

                        return this.options
                            .filter((option) => option.label.toLowerCase().includes(query) || String(option.id).startsWith(id))
                            .slice(0, 60);
                    },

                    // A bare ID ("1057" or "#1057") picks that product once typing pauses.
                    byId() {
                        const match = this.text.trim().match(/^#?(\d+)$/);

                        return match ? this.options.find((option) => option.id === Number(match[1])) : null;
                    },

                    onInput() {
                        this.open = true;
                        this.active = 0;
                        clearTimeout(this.timer);
                        this.timer = setTimeout(() => {
                            const hit = this.byId();
                            if (hit) this.choose(hit);
                        }, 600);
                    },

                    move(step) {
                        this.open = true;
                        this.active = Math.max(0, Math.min(this.filtered.length - 1, this.active + step));
                    },

                    pickActive() {
                        const hit = this.byId() ?? this.filtered[this.active];
                        if (hit) this.choose(hit);
                    },

                    choose(option) {
                        clearTimeout(this.timer);
                        this.open = false;
                        this.text = option.label;
                        if (option.id === this.value) return;
                        this.value = option.id;
                        this.$wire.call(config.method, ...config.args, option.id);
                    },

                    // Leaving the field without a valid match restores the previous selection.
                    onBlur() {
                        const exact = this.options.find((option) => option.label === this.text.trim()) ?? this.byId();
                        if (exact) {
                            this.choose(exact);
                        } else {
                            this.close(true);
                        }
                    },

                    close(restore = false) {
                        this.open = false;
                        if (restore) this.text = this.labelFor(this.value);
                    },
                }));
            });
        </script>

        @livewire('admin.sales-document-builder', ['document' => $document])
    </div>
</div>
@endsection
