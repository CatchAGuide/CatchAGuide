@extends('layouts.app-v2-1')

@section('title', ucwords(translate('Allgemeine Geschäftsbedingungen')))
@section('meta_robots')
    <meta name="robots" content="noindex, nofollow">
@endsection

@section('content')
<div class="container">
    <section class="page-header">
        <div class="page-header__bottom breadcrumb-container guiding">
            <div class="page-header__bottom-inner">
                <ul class="thm-breadcrumb list-unstyled">
                    <li><a href="{{ route('welcome') }}">@lang('message.home')</a></li>
                    <li><span><i class="fas fa-solid fa-chevron-right"></i></span></li>
                    <li class="active">@lang('message.term-conditions')</li>
                </ul>
            </div>
        </div>
    </section>
</div>

<section class="about-pages terms-dynamic py-4">
    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h2">@lang('message.term-conditions')</h1>
                <h3>ALLGEMEINE GESCHÄFTS- UND NUTZUNGSBEDINGUNGEN</h3>
                <p>für die Nutzung sowie den Bezug oder Absatz von Waren oder Dienstleistungen auf der „Catch A Guide“-Plattform</p>
            </div>
        </div>

        <div class="row">
            <aside class="col-lg-3 mb-4 mb-lg-0">
                <nav class="terms-sidebar list-group sticky-top" style="top: 100px;" id="termsSidebar">
                    @foreach($sections as $index => $section)
                        @php $translation = $section->translations->first(); @endphp
                        <a href="#section-{{ $section->id }}"
                           class="list-group-item list-group-item-action terms-nav-link {{ $loop->first ? 'active' : '' }}"
                           data-section-id="section-{{ $section->id }}">
                            {{ ($index + 1) }}) {{ $translation->title }}
                        </a>
                    @endforeach
                </nav>
            </aside>

            <div class="col-lg-9">
                @foreach($sections as $index => $section)
                    @php $translation = $section->translations->first(); @endphp
                    <article id="section-{{ $section->id }}" class="terms-section mb-5" data-section-id="section-{{ $section->id }}">
                        <h4 class="pt-2">{{ ($index + 1) }}) {{ $translation->title }}</h4>
                        <div class="terms-section-content">
                            {!! $translation->content !!}
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
@endsection

@push('js_push')
<script>
(function () {
    var links = document.querySelectorAll('.terms-nav-link');
    var sections = document.querySelectorAll('.terms-section');
    if (!links.length || !sections.length) {
        return;
    }

    function setActive(id) {
        links.forEach(function (link) {
            link.classList.toggle('active', link.getAttribute('data-section-id') === id);
        });
    }

    links.forEach(function (link) {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            var targetId = link.getAttribute('data-section-id');
            var target = document.getElementById(targetId);
            if (target) {
                var offset = 120;
                var top = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({ top: top, behavior: 'smooth' });
                setActive(targetId);
            }
        });
    });

    function onScroll() {
        var current = sections[0].getAttribute('data-section-id');
        var scrollPos = window.pageYOffset + 140;

        sections.forEach(function (section) {
            if (section.offsetTop <= scrollPos) {
                current = section.getAttribute('data-section-id');
            }
        });

        setActive(current);
    }

    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
</script>
@endpush
