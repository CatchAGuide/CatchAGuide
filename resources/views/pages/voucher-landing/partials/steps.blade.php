@php($copy = 'guidings.voucher_landing.')
<section class="vl-section vl-section--night" id="ablauf">
    <div class="vl-container vl-stack vl-stack--lg">
        <h2 class="vl-h2">{{ __($copy.'steps_title') }}</h2>
        <ol class="vl-steps">
            @foreach([1 => 'pen', 2 => 'chat', 3 => 'gift', 4 => 'fish'] as $n => $icon)
                <li @class(['vl-step', 'vl-step--first' => $n === 1])>
                    <span class="vl-step__icon">@include('pages.voucher-landing.partials.icon', ['name' => $icon])</span>
                    <div class="vl-step__copy">
                        <span class="vl-step__label vl-mono">{{ __($copy.'step', ['n' => sprintf('%02d', $n)]) }}</span>
                        <h3 class="vl-h3">{{ __($copy.'step_'.$n.'_title') }}</h3>
                        <p>{{ __($copy.'step_'.$n.'_text') }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
