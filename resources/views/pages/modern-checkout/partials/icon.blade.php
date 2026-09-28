{{-- Stroke icons used by the tour checkout. Usage: @include('pages.modern-checkout.partials.icon', ['name' => 'clock', 'size' => 13]) --}}
@php
    $paths = [
        'send' => '<path d="M22 2 11 13"/><path d="M22 2l-7 20-4-9-9-4 20-7z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'coin' => '<circle cx="12" cy="12" r="9"/><path d="M15 9a4 4 0 1 0 0 6"/><path d="M8 11h5M8 13.5h5"/>',
        'pin' => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="8.5" cy="9.5" r="1.5"/><path d="m21 15-5-5-8 8"/>',
        'check' => '<path d="m20 6-11 11-5-5"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'cash' => '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2.5"/>',
        'transfer' => '<path d="M3 10h18M5 10V6l7-3 7 3v4M6 10v8M18 10v8M3 18h18"/>',
        'paypal' => '<path d="M6 21 8.5 4h5.5a4 4 0 0 1 0 8H9"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/>',
        'message' => '<path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>',
        'x' => '<path d="M18 6 6 18M6 6l12 12"/>',
    ];
@endphp
<svg class="tc-icon {{ $class ?? '' }}" width="{{ $size ?? 14 }}" height="{{ $size ?? 14 }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="{{ $stroke ?? 1.8 }}" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">{!! $paths[$name] ?? '' !!}</svg>
