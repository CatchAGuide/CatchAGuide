<div class="ga-page">
    <header class="ga-page__header">
        <div>
            <h1 class="ga-page__title">{{ __('guide_area.tours.title') }}</h1>
            <p class="ga-page__subtitle">{{ __('guide_area.tours.subtitle') }}</p>
        </div>
        <a href="{{ route('profile.newguiding') }}" class="ga-btn ga-btn--dark">
            <i class="fas fa-plus" aria-hidden="true"></i> {{ __('guide_area.tours.create') }}
        </a>
    </header>

    @unless ($canPublish)
        <p class="ga-note">{{ __('guide_area.tours.pending_note') }}</p>
    @endunless

    <div class="ga-toolbar">
        <div class="ga-tabs" role="tablist" aria-label="{{ __('guide_area.tours.tabs_label') }}">
            @foreach ($tabs as $tab)
                <button type="button" role="tab" class="ga-tabs__tab {{ $status === $tab ? 'is-active' : '' }}"
                        aria-selected="{{ $status === $tab ? 'true' : 'false' }}" wire:click="setStatus('{{ $tab }}')">
                    {{ __('guide_area.tours.tabs.'.$tab) }}
                    <span class="ga-tabs__count">{{ $counts[$tab] ?? 0 }}</span>
                </button>
            @endforeach
        </div>
        <label class="ga-search">
            <span class="ga-visually-hidden">{{ __('guide_area.tours.search') }}</span>
            <i class="fas fa-search" aria-hidden="true"></i>
            <input type="search" class="ga-input" wire:model.live.debounce.400ms="search" placeholder="{{ __('guide_area.tours.search') }}">
        </label>
    </div>

    @if ($tours->isEmpty())
        <div class="ga-empty">
            <i class="fas fa-fish" aria-hidden="true"></i>
            <p>{{ $search !== '' ? __('guide_area.tours.empty_search') : __('guide_area.tours.empty') }}</p>
            @if ($search === '' && $status === 'all')
                <a href="{{ route('profile.newguiding') }}" class="ga-btn ga-btn--dark">{{ __('guide_area.tours.create') }}</a>
            @endif
        </div>
    @else
        <ul class="ga-tour-list">
            @foreach ($tours as $tour)
                <li class="ga-tour" wire:key="tour-{{ $tour['id'] }}">
                    <img src="{{ $tour['image'] }}" alt="" class="ga-tour__img" width="120" height="90" loading="lazy">
                    <div class="ga-tour__body">
                        <div class="ga-tour__head">
                            <span class="ga-status {{ ['live' => 'ga-status--success', 'draft' => 'ga-status--warning', 'deactivated' => ''][$tour['status']] }}">{{ $tour['status_label'] }}</span>
                            @if ($tour['voucher_enabled'])
                                <span class="ga-badge"><i class="fas fa-gift" aria-hidden="true"></i> {{ __('guide_area.tours.voucher_on') }}</span>
                            @endif
                        </div>
                        <h2 class="ga-tour__title">{{ $tour['title'] }}</h2>
                        @if ($tour['location'] !== '')
                            <p class="ga-tour__meta"><i class="fas fa-map-marker-alt" aria-hidden="true"></i> {{ $tour['location'] }}</p>
                        @endif
                        @if ($tour['status'] !== 'live' && $tour['gaps'] !== [])
                            <details class="ga-tour__gaps" @if (isset($gapsFor[$tour['id']])) open @endif>
                                <summary>{{ trans_choice('guide_area.tours.gaps_summary', count($tour['gaps']), ['count' => count($tour['gaps'])]) }}</summary>
                                <ul>
                                    @foreach ($tour['gap_labels'] as $label)
                                        <li>{{ $label }}</li>
                                    @endforeach
                                </ul>
                            </details>
                        @endif
                    </div>
                    <div class="ga-tour__actions">
                        <a href="{{ $tour['edit_url'] }}" class="ga-btn ga-btn--outline ga-btn--sm">{{ __('guide_area.tours.edit') }}</a>
                        @if ($tour['public_url'])
                            <a href="{{ $tour['public_url'] }}" class="ga-btn ga-btn--ghost ga-btn--sm" target="_blank" rel="noopener">{{ __('guide_area.tours.view') }}</a>
                        @endif
                        @if ($tour['status'] === 'live')
                            <button type="button" class="ga-btn ga-btn--danger-outline ga-btn--sm" wire:click="deactivate({{ $tour['id'] }})"
                                    wire:confirm="{{ __('guide_area.tours.deactivate_confirm') }}">{{ __('guide_area.tours.deactivate') }}</button>
                        @elseif ($canPublish)
                            <button type="button" class="ga-btn ga-btn--dark ga-btn--sm" wire:click="goLive({{ $tour['id'] }})"
                                    @disabled(! $tour['can_go_live']) title="{{ $tour['can_go_live'] ? '' : __('guide_area.tours.go_live_blocked') }}">{{ __('guide_area.tours.go_live') }}</button>
                        @endif
                        <button type="button" class="ga-btn ga-btn--ghost ga-btn--sm" wire:click="duplicate({{ $tour['id'] }})"
                                wire:confirm="{{ __('guide_area.tours.duplicate_confirm') }}" wire:loading.attr="disabled" wire:target="duplicate({{ $tour['id'] }})">
                            <i class="far fa-copy" aria-hidden="true"></i> {{ __('guide_area.tours.duplicate') }}
                        </button>
                    </div>
                </li>
            @endforeach
        </ul>

        {{ $tours->links('livewire.guide.partials.pager') }}
    @endif
</div>
