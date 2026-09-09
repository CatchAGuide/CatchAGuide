@props([
    'items',
    'title',
])

@php
    $faqItems = collect($items)->map(function ($item) {
        if (is_array($item)) {
            $question = (string) ($item['question'] ?? '');
            $answer = (string) ($item['answer'] ?? '');
        } else {
            $question = (string) ($item->question ?? '');
            $answer = (string) ($item->answer ?? '');
        }

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    })->filter(fn (array $item) => $item['question'] !== '')->values();
@endphp

@if($faqItems->isNotEmpty())
    <section {{ $attributes->merge(['class' => 'vacation-faq']) }}>
        <div class="vacation-faq__inner">
            <x-vacation.section-heading :title="$title" />

            <div class="vacation-faq__list" data-vacation-faq>
                @foreach($faqItems as $item)
                    <div class="vacation-faq__item">
                        <button type="button" class="vacation-faq__q" data-vacation-faq-toggle>
                            <span>{{ $item['question'] }}</span>
                            <span class="vacation-faq__chev" aria-hidden="true">⌄</span>
                        </button>
                        <div class="vacation-faq__a">
                            {!! clean_html($item['answer']) !!}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

@once
<script>
(function () {
    document.querySelectorAll('[data-vacation-faq]').forEach(function (faqRoot) {
        faqRoot.addEventListener('click', function (event) {
            var btn = event.target.closest('[data-vacation-faq-toggle]');
            if (!btn || !faqRoot.contains(btn)) {
                return;
            }
            var item = btn.closest('.vacation-faq__item');
            if (!item) {
                return;
            }
            var willOpen = !item.classList.contains('is-open');
            faqRoot.querySelectorAll('.vacation-faq__item.is-open').forEach(function (openItem) {
                openItem.classList.remove('is-open');
            });
            if (willOpen) {
                item.classList.add('is-open');
            }
        });
    });
})();
</script>
@endonce
