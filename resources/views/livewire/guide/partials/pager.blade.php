{{-- Guide Area pager for Livewire paginators: previous / "page X of Y" / next. --}}
@if ($paginator->hasPages())
    <nav class="ga-pager" aria-label="{{ __('guide_area.pager.label') }}">
        <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="previousPage('{{ $paginator->getPageName() }}')"
                @disabled($paginator->onFirstPage()) aria-label="{{ __('guide_area.pager.previous') }}">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>
        <span class="ga-pager__text">{{ __('guide_area.pager.page_of', ['page' => $paginator->currentPage(), 'pages' => $paginator->lastPage()]) }}</span>
        <button type="button" class="ga-btn ga-btn--outline ga-btn--sm" wire:click="nextPage('{{ $paginator->getPageName() }}')"
                @disabled(! $paginator->hasMorePages()) aria-label="{{ __('guide_area.pager.next') }}">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
    </nav>
@endif
