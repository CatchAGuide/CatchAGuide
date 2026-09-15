<div class="accommodation-card__panel-title">{{ __('vacations.details') }}</div>
<ul class="accommodation-card__fact-list">
    @foreach($accommodation['accommodation_details'] as $detail)
        <li class="accommodation-card__fact-row">
            <span class="accommodation-card__fact-label">{{ translated_catalog_label($detail) }}</span>
            <span class="accommodation-card__fact-value">{{ is_numeric($detail['value'] ?? null) ? $detail['value'] : translate($detail['value'] ?? '') }}</span>
        </li>
    @endforeach
</ul>
