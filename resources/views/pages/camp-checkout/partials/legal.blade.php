<p class="cc-legal">
    {!! __('checkout.tour.legal', [
        'terms' => '<a href="'.e(route('law.agb')).'" target="_blank" rel="noopener">'.e(__('checkout.tour.legal_terms')).'</a>',
        'privacy' => '<a href="'.e(route('law.data-protection')).'" target="_blank" rel="noopener">'.e(__('checkout.tour.legal_privacy')).'</a>',
    ]) !!}
</p>
