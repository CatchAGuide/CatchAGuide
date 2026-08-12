@php
    $locales = $locales ?? [];
@endphp

<td class="text-center category-index__locale-cell">
    @foreach($locales as $locale)
        @if($locale === 'de')
            <i class="fi fi-de category-index__locale-flag" title="Deutsch"></i>
        @elseif($locale === 'en')
            <i class="fi fi-gb category-index__locale-flag" title="English"></i>
        @endif
    @endforeach
</td>
