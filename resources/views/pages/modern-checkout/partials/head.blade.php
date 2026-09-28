<header class="tc__head">
    <h1 class="tc__title">{{ $title }}</h1>

    <ol class="tc-steps">
        @foreach ($steps as $icon => $key)
            <li class="tc-steps__item">
                <span class="tc-steps__icon">@include('pages.modern-checkout.partials.icon', ['name' => $icon, 'size' => 15])</span>
                <span class="tc-steps__label">{{ __($key) }}</span>
            </li>
        @endforeach
    </ol>

    @if ($intro)
        @include($intro, $introData)
    @endif
</header>
