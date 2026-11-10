@php($copy = 'guidings.voucher_landing.')
<section class="vl-section vl-section--sand vl-section--quiet">
    <div class="vl-container vl-stack">
        <h2 class="vl-h2">{{ __($copy.'why_title') }}</h2>
        <div class="vl-why">
            @foreach([1 => 'gift', 2 => 'compass', 3 => 'fish'] as $n => $icon)
                <div class="vl-why__card">
                    <span class="vl-why__icon">@include('pages.voucher-landing.partials.icon', ['name' => $icon])</span>
                    <h3 class="vl-h3">{{ __($copy.'why_'.$n.'_title') }}</h3>
                    <p>{{ __($copy.'why_'.$n.'_text') }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>
