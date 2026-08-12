@php
    $scopes = $scopes ?? [];
    $completeness = $completeness ?? [];
@endphp

@foreach($scopes as $scope)
    @php
        $filledDe = $completeness[$scope]['de'] ?? false;
        $filledEn = $completeness[$scope]['en'] ?? false;
        $state = ($filledDe && $filledEn) ? 'complete' : (($filledDe || $filledEn) ? 'partial' : 'empty');
    @endphp
    <td class="text-center category-index__scope-cell">
        <span class="category-index__scope-badge category-index__scope-badge--{{ $state }}"
            title="{{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}">
            {{ \App\Domain\CategoryPage\CategoryPageScope::label($scope) }}
        </span>
    </td>
@endforeach
