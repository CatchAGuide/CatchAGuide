@if ($paginator->hasPages())
<ul class="pagination p-0 my-1">
    {{-- Double Arrow First Page Link --}}
    @if ($paginator->onFirstPage())
        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
    @else
        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">&laquo;</a></li>
    @endif

    {{-- First Page Link --}}
    @if ($paginator->onFirstPage())
        <li class="page-item active"><span class="page-link">1</span></li>
    @else
        <li class="page-item"><a class="page-link" href="{{ $paginator->url(1) }}">1</a></li>
    @endif

    {{-- Previous Page Link --}}
    @if ($paginator->currentPage() > 1)
        <li class="page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}">&lt;</a></li>
    @endif

    {{-- Pages Before Current Page --}}
    @for ($i = max(2, $paginator->currentPage() - 2); $i <= min($paginator->lastPage() - 1, $paginator->currentPage() + 2); $i++)
        @if ($i == $paginator->currentPage())
            <li class="page-item active"><span class="page-link">{{ $i }}</span></li>
        @else
            <li class="page-item"><a class="page-link" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
        @endif
    @endfor

    {{-- Next Page Link --}}
    @if ($paginator->hasMorePages())
        <li class="page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}">&gt;</a></li>
    @endif

    {{-- Last Page Link --}}
    @if ($paginator->currentPage() != $paginator->lastPage())
        <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
    @else
        <li class="page-item active"><span class="page-link">{{ $paginator->lastPage() }}</span></li>
    @endif

    {{-- Double Arrow Last Page Link --}}
    @if (!$paginator->hasMorePages())
        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
    @else
        <li class="page-item"><a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">&raquo;</a></li>
    @endif
</ul>
@endif
