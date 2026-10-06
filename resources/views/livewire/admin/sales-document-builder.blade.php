@use('App\Services\Sales\SalesFormat')
@php
    $t = fn (string $key, array $replace = []) => __('sales.builder.'.$key, $replace);
    $status = $document?->status;
    $steps = ['draft', 'sent', 'accepted', 'confirmed'];
    $statusStep = match ($status?->value) {
        null, 'draft' => 0,
        'sent', 'viewed' => 1,
        'accepted' => 2,
        'confirmed' => 3,
        default => null,
    };
    $money = fn ($amount) => SalesFormat::money($amount, app()->getLocale());
@endphp
<div class="sb" x-data x-on:sales-focus.window="$nextTick(() => document.querySelector($event.detail.selector)?.focus())">
    <div class="sb-top">
        <div>
            <h1 class="sb-title">{{ $t('heading') }}</h1>
            <div class="sb-crumbs">
                <a href="{{ route('admin.index') }}">Admin</a> › {{ $t('crumb_sales') }} ›
                <a href="{{ route('admin.sales.offers.index') }}">{{ $t('crumb_offers') }}</a> ›
                <span>{{ $document?->number ?? $t('new_document') }}</span>
            </div>
        </div>
        <div class="sb-status">
            @if($statusStep !== null)
                <div class="sb-steps" aria-label="{{ $t('status') }}">
                    @foreach($steps as $i => $step)
                        <span @class(['sb-step', 'is-done' => $i < $statusStep, 'is-now' => $i === $statusStep])>{{ __('sales.status.'.$step) }}</span>
                        @unless($loop->last)<span class="sb-step-arrow">›</span>@endunless
                    @endforeach
                </div>
            @else
                <span class="badge bg-{{ $status->badge() }}">{{ $status->label() }}</span>
            @endif
        </div>
    </div>

    <div class="sb-grid">
        <div class="sb-form">
            @if($sourceRequest)
                @include('livewire.admin.sales.source-request', ['request' => $sourceRequest])
            @endif

            {{-- Recipient --}}
            <section class="sb-panel">
                <h2>{{ $t('recipient') }}</h2>
                <div class="sb-seg" role="group">
                    <button type="button" wire:click="setRecipientMode('customer')" @class(['is-on' => $header['recipient_mode'] === 'customer'])>{{ $t('select_customer') }}</button>
                    <button type="button" wire:click="setRecipientMode('contact')" @class(['is-on' => $header['recipient_mode'] !== 'customer'])>{{ $t('enter_contact') }}</button>
                </div>
                @if($header['recipient_mode'] === 'customer')
                    <div class="sb-field sb-customer">
                        <span class="sb-label">{{ $t('search_customer') }}</span>
                        <div class="sb-customer__box">
                            <input type="search" class="form-control form-control-sm" wire:model.live.debounce.300ms="customerSearch" placeholder="{{ $t('search_customer_placeholder') }}" autocomplete="off">
                            @if($customerResults !== [])
                                <ul class="sb-combo__list">
                                    @foreach($customerResults as $result)
                                        <li wire:key="customer-{{ $result['id'] }}" wire:click="selectCustomer({{ $result['id'] }})">{{ $result['label'] }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        @if($header['customer_id'])
                            <small class="text-muted">{{ $t('linked_customer', ['id' => $header['customer_id']]) }}</small>
                        @endif
                    </div>
                @endif
                <div class="sb-row">
                    <label class="sb-field"><span class="sb-label">{{ $t('first_name') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="header.first_name"></label>
                    <label class="sb-field"><span class="sb-label">{{ $t('last_name') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="header.last_name"></label>
                    <label class="sb-field"><span class="sb-label">{{ $t('email') }} *</span><input type="email" class="form-control form-control-sm" wire:model.live.debounce.400ms="header.email"></label>
                    <label class="sb-field"><span class="sb-label">{{ $t('phone') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="header.phone"></label>
                </div>
                <label class="sb-field"><span class="sb-label">{{ $t('travellers') }}</span><input class="form-control form-control-sm" wire:model.live.debounce.400ms="header.travellers" placeholder="{{ $t('travellers_placeholder') }}"></label>
            </section>

            {{-- Settings --}}
            <section class="sb-panel">
                <h2>{{ $t('settings') }}</h2>
                <div class="sb-row">
                    <label class="sb-field"><span class="sb-label">{{ $t('language') }}</span>
                        <select class="form-select form-select-sm" wire:model.live="header.language">
                            <option value="de">Deutsch</option>
                            <option value="en">English</option>
                        </select>
                    </label>
                    <label class="sb-field"><span class="sb-label">{{ $t('valid_until') }}</span><input type="date" class="form-control form-control-sm" wire:model.live="header.valid_until"></label>
                </div>
                @if($previewOutput === 'confirmation')
                    <label class="sb-field" wire:key="intro-confirmation"><span class="sb-label">{{ $t('intro_confirmation') }}</span><textarea class="form-control form-control-sm" rows="3" wire:model.live.debounce.500ms="header.intro_confirmation" placeholder="{{ $introPlaceholder }}"></textarea></label>
                @else
                    <label class="sb-field" wire:key="intro-offer"><span class="sb-label">{{ $t('intro_offer') }}</span><textarea class="form-control form-control-sm" rows="3" wire:model.live.debounce.500ms="header.intro_offer" placeholder="{{ $introPlaceholder }}"></textarea></label>
                @endif
            </section>

            {{-- Products --}}
            <section class="sb-panel">
                <h2>{{ $t('products') }}</h2>
                <div class="sb-cards">
                    @forelse($cards as $i => $card)
                        @php($group = $groups[$card['key']] ?? null)
                        <div class="sb-card" wire:key="card-{{ $card['key'] }}">
                            <div class="sb-card__head">
                                <span class="sb-tag sb-tag--{{ $card['type'] }}">{{ $t('type_'.$card['type']) }}</span>
                                @if(! empty($card['product']['url']))
                                    <a class="sb-card__link" href="{{ $card['product']['url'] }}" target="_blank" rel="noopener">{{ preg_replace('#^https?://#', '', $card['product']['url']) }} ↗</a>
                                @endif
                                <span class="sb-card__tools">
                                    <button type="button" class="sb-x" wire:click="moveCard({{ $i }}, -1)" @disabled($i === 0) aria-label="{{ $t('move_up') }}">↑</button>
                                    <button type="button" class="sb-x" wire:click="moveCard({{ $i }}, 1)" @disabled($loop->last) aria-label="{{ $t('move_down') }}">↓</button>
                                    <button type="button" class="sb-x" wire:click="removeCard({{ $i }})" aria-label="{{ $t('remove') }}">×</button>
                                </span>
                            </div>
                            <div class="sb-card__body">
                                @if(in_array($card['key'], $staleCards, true))
                                    <div class="sb-hint-box">{{ $t('prices_changed') }} <button type="button" class="btn btn-link btn-sm p-0" wire:click="refreshProduct({{ $i }})">{{ $t('update_prices') }}</button></div>
                                @endif
                                @include('livewire.admin.sales.card-'.$card['type'])
                            </div>
                            @if($group && ($group['errors'] !== [] || $group['warnings'] !== []))
                                <div class="sb-warns">
                                    @foreach($group['errors'] as $error)<div class="sb-err">⛔ {{ $error }}</div>@endforeach
                                    @foreach($group['warnings'] as $warning)<div class="sb-warn">⚠ {{ $warning }}</div>@endforeach
                                </div>
                            @endif
                            <div class="sb-card__subtotal"><span>{{ $t('subtotal') }}</span><span>{{ $money($group['subtotal'] ?? 0) }}</span></div>
                        </div>
                    @empty
                        <div class="sb-hint">{{ $t('no_products') }}</div>
                    @endforelse
                </div>
                <div class="sb-adds">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addCard('tour')">+ {{ $t('type_tour') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addCard('camp')">+ {{ $t('type_camp') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addCard('trip')">+ {{ $t('type_trip') }}</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="addCard('custom')">+ {{ $t('type_custom') }}</button>
                </div>
            </section>

            {{-- Details for the customer --}}
            <section class="sb-panel">
                <h2>{{ $t('details') }}</h2>
                <span class="sb-label">{{ $t('not_included') }}</span>
                <div class="sb-lines">
                    @forelse($header['not_included'] as $n => $line)
                        <div class="sb-line" wire:key="excl-{{ $n }}-{{ count($header['not_included']) }}">
                            <input class="form-control form-control-sm" data-not-included="{{ $n }}" wire:model.live.debounce.400ms="header.not_included.{{ $n }}" placeholder="{{ $t('not_included_placeholder') }}">
                            <button type="button" class="sb-x" wire:click="removeNotIncluded({{ $n }})" aria-label="{{ $t('remove_line') }}">×</button>
                        </div>
                    @empty
                        <div class="sb-hint">{{ $t('not_included_empty') }}</div>
                    @endforelse
                </div>
                <button type="button" class="btn btn-outline-secondary btn-sm align-self-start" wire:click="addNotIncluded">+ {{ $t('add_line') }}</button>
                <label class="sb-field"><span class="sb-label">{{ $t('good_to_know') }}</span><textarea class="form-control form-control-sm" rows="3" wire:model.live.debounce.500ms="header.good_to_know"></textarea></label>
                <label class="sb-field"><span class="sb-label">{{ $t('payment_note') }}</span><textarea class="form-control form-control-sm" rows="2" wire:model.live.debounce.500ms="header.payment_note" placeholder="{{ $t('payment_note_placeholder') }}"></textarea></label>
            </section>

            {{-- Totals bar --}}
            <div class="sb-totals">
                <div>
                    <div class="sb-totals__meta">{{ trans_choice('sales.builder.lines', $quote->lineCount(), ['count' => $quote->lineCount()]) }} · {{ $periodLabel ?? $t('no_dates') }}</div>
                    <div class="sb-totals__big">{{ $money($quote->total) }}</div>
                </div>
                @if($notice)
                    <div class="sb-totals__notice sb-totals__notice--{{ $noticeType }}" role="status">{{ $notice }}</div>
                @endif
                <div class="sb-totals__actions">
                    <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="saveDraft" wire:loading.attr="disabled">{{ $t('save_draft') }}</button>
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="sendOffer" wire:loading.attr="disabled">{{ $document?->offer_sent_at ? $t('resend_offer') : $t('send_offer') }}</button>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="sendConfirmation" wire:loading.attr="disabled">{{ $document?->confirmation_sent_at ? $t('resend_confirmation') : $t('send_confirmation') }}</button>
                </div>
                @if($checks['errors'] !== [] || $checks['warnings'] !== [])
                    <div class="sb-totals__warns">
                        @foreach(array_diff($checks['errors'], $quote->errors()) as $error)<div class="sb-err">⛔ {{ $error }}</div>@endforeach
                        @if($checks['errors'] !== [])
                            <button type="button" class="sb-totals__jump" x-on:click="document.querySelector('.sb-card .sb-err, .sb-totals .sb-err')?.scrollIntoView({ behavior: 'smooth', block: 'center' })">{{ trans_choice('sales.builder.errors_count', count($checks['errors']), ['count' => count($checks['errors'])]) }} ↓</button>
                        @endif
                        @if($checks['warnings'] !== [])<div class="sb-hint">{{ trans_choice('sales.builder.warnings_count', count($checks['warnings']), ['count' => count($checks['warnings'])]) }}</div>@endif
                    </div>
                @endif
            </div>

            @if($events->isNotEmpty())
                <section class="sb-panel">
                    <h2>{{ $t('status_log') }}</h2>
                    <ul class="sb-log">
                        @foreach($events as $event)
                            <li><span>{{ $event->created_at?->format('d.m.Y H:i') }}</span> {{ $event->type->label() }}
                                <small class="text-muted">· {{ $event->employee?->name ?? __('sales.actor.'.$event->actor) }}</small></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        {{-- Live preview --}}
        <div class="sb-preview">
            <div class="sb-preview__bar">
                <div class="sb-seg">
                    <button type="button" wire:click="setPreviewView('page')" @class(['is-on' => $previewView === 'page'])>{{ $t('preview_page') }}</button>
                    <button type="button" wire:click="setPreviewView('mail')" @class(['is-on' => $previewView === 'mail'])>{{ $t('preview_mail') }}</button>
                </div>
                <div class="sb-seg">
                    <button type="button" wire:click="setPreviewOutput('offer')" @class(['is-on' => $previewOutput === 'offer'])>{{ $t('output_offer') }}</button>
                    <button type="button" wire:click="setPreviewOutput('confirmation')" @class(['is-on' => $previewOutput === 'confirmation'])>{{ $t('output_confirmation') }}</button>
                </div>
            </div>
            <div class="sb-frame">
                @if($previewView === 'mail')
                    <dl class="sb-mailhead">
                        <dt>{{ $t('mail_to') }}</dt><dd>{{ $mailRecipients['to'] }}</dd>
                        @if($mailRecipients['cc'] !== '')<dt>CC</dt><dd>{{ $mailRecipients['cc'] }}</dd>@endif
                        @if($mailRecipients['bcc'] !== '')<dt>BCC</dt><dd>{{ $mailRecipients['bcc'] }}</dd>@endif
                        <dt>{{ $t('mail_subject') }}</dt><dd><strong>{{ $doc['subject'] }}</strong></dd>
                    </dl>
                @else
                    <div class="sb-urlbar">
                        <span>{{ $previewUrl }}</span>
                        @if($document)
                            <a href="{{ $doc['url'] }}" target="_blank" rel="noopener" class="ms-auto">{{ $t('open_customer_page') }} ↗</a>
                        @endif
                    </div>
                @endif
                <iframe class="sb-iframe" title="{{ $t('preview') }}" srcdoc="{{ $previewHtml }}" sandbox="allow-same-origin allow-popups"></iframe>
            </div>
        </div>
    </div>
</div>
