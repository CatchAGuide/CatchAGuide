@php
    $copy = 'guidings.voucher_landing.';
    $tiles = $landing->tiles();
@endphp
@if($tiles !== [] && ! $success)
<section class="vl-section vl-section--paper vl-section--quiet">
    <div class="vl-container vl-stack">
        <div class="vl-intro">
            <h2 class="vl-h2">{{ __($copy.'where_title') }}</h2>
            <p>{{ __($copy.'where_text') }}</p>
        </div>
        <div class="vl-tiles">
            @foreach($tiles as $tile)
                <button type="button" class="vl-tile" @click="pickTile(@js($tile['country']), @js($tile['region']))"
                        @if($tile['image']) style="background-image: url('{{ $tile['image'] }}')" @endif>
                    <span class="vl-tile__shade" aria-hidden="true"></span>
                    @if($tile['species'])
                        <span class="vl-tile__tag">{{ $tile['species'] }}</span>
                    @endif
                    <span class="vl-tile__body">
                        <span class="vl-tile__country vl-mono">{{ $tile['country'] }}</span>
                        <span class="vl-tile__title">{{ $tile['region'] }}</span>
                        <span class="vl-tile__foot">
                            <span>{{ $tile['from'] }}</span>
                            <span class="vl-tile__go">@include('pages.voucher-landing.partials.icon', ['name' => 'arrow'])</span>
                        </span>
                    </span>
                </button>
            @endforeach
            <button type="button" class="vl-tile vl-tile--open" @click="pickTile(null, null)">
                <span class="vl-tile__open-icon">@include('pages.voucher-landing.partials.icon', ['name' => 'compass'])</span>
                <span class="vl-tile__title">{{ __($copy.'tile_open_title') }}</span>
                <span class="vl-tile__text">{{ __($copy.'tile_open_text') }}</span>
                <span class="vl-tile__cta">{{ __($copy.'tile_open_cta') }} @include('pages.voucher-landing.partials.icon', ['name' => 'arrow'])</span>
            </button>
        </div>
    </div>
</section>
@endif
