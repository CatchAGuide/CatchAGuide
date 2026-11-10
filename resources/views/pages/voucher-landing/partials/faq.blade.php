{{-- Questions come from Admin › FAQ › Gift voucher (Faq page "gift-voucher") in the page language. --}}
@php
    $copy = 'guidings.voucher_landing.';
    $faqs = $landing->faqs();
@endphp
@if($faqs->isNotEmpty())
@include('components.seo.json-ld', ['data' => [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqs->map(fn ($faq) => [
            '@type' => 'Question',
            'name' => strip_tags((string) $faq->question),
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => strip_tags((string) $faq->answer)],
        ])->values()->all(),
]])
<section class="vl-section vl-section--sand">
    <div class="vl-container vl-container--narrow vl-stack">
        <h2 class="vl-h2">{{ __($copy.'faq_title') }}</h2>
        <div class="vl-faq" x-data="{ open: 0 }">
            @foreach($faqs as $i => $faq)
                <div class="vl-faq__item" :class="{ 'is-open': open === {{ $i }} }">
                    <button type="button" class="vl-faq__q" @click="open = open === {{ $i }} ? -1 : {{ $i }}" :aria-expanded="open === {{ $i }}" aria-controls="vl-faq-{{ $i }}">
                        <span>{{ $faq->question }}</span>
                        <span class="vl-faq__chev">@include('pages.voucher-landing.partials.icon', ['name' => 'chevron'])</span>
                    </button>
                    <div class="vl-faq__a" id="vl-faq-{{ $i }}" x-show="open === {{ $i }}" x-transition.opacity @if($i !== 0) x-cloak @endif>
                        {!! clean_html($faq->answer) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
