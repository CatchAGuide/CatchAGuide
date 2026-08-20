@php $faqs = get_faqs_by_page('home'); @endphp
@if(count($faqs))
<section class="gl-faq">
    <div class="cag-home-container">
        <h2 class="gl-faq__title">{{ __('homepage.faq-title') }}</h2>
        <div class="gl-faq__list" data-gl-faq>
            @foreach($faqs as $faq)
                <div class="gl-faq__item">
                    <button type="button" class="gl-faq__q" data-gl-faq-toggle>
                        <span>{{ app()->getlocale() == 'de' ? $faq->question : translate($faq->question) }}</span>
                        <span class="gl-faq__chev" aria-hidden="true">⌄</span>
                    </button>
                    <div class="gl-faq__a">
                        {!! app()->getlocale() == 'de' ? clean_html($faq->answer) : clean_html(translate($faq->answer)) !!}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
