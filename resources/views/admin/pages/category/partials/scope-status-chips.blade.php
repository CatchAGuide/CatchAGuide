@php
    $scopes = $scopes ?? [];
    $completeness = $completeness ?? [];
@endphp

<td class="text-center">
    <div class="d-flex flex-wrap justify-content-center gap-1">
        @foreach($scopes as $scope)
            @php
                $filledDe = $completeness[$scope]['de'] ?? false;
                $filledEn = $completeness[$scope]['en'] ?? false;
                $state = ($filledDe && $filledEn)
                    ? 'complete'
                    : (($filledDe || $filledEn) ? 'partial' : 'empty');
            @endphp
            <span
                class="category-index__scope-badge category-index__scope-badge--{{ $state }}"
                title="{{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}"
            >
                {{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}
            </span>
        @endforeach
    </div>
</td>

