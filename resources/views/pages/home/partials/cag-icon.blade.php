@php
    $name = $name ?? 'rod';
    $size = (int) ($size ?? 20);
    $iconClass = trim('cag-icon cag-icon--'.$name.' '.($iconClass ?? ''));
    $boxes = [
        'rod' => '0 0 32 32',
        'fish' => '0 0 32 32',
        'camp' => '0 0 32 32',
        'globe' => '0 0 32 32',
    ];
    $viewBox = $boxes[$name] ?? '0 0 24 24';
@endphp
<svg
    class="{{ $iconClass }}"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="{{ $viewBox }}"
    fill="none"
    stroke="currentColor"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>
    @switch($name)
        @case('rod')
            <path d="M6 5c8 1 13 6 15 14"></path>
            <path d="M21 19l1.6 4"></path>
            <path d="M22.6 23c-2.4.6-4-.4-4.4-2"></path>
            <path d="M4 27c2.6 0 2.6-1.8 5.2-1.8S12 27 14.6 27s2.6-1.8 5.2-1.8S22.4 27 25 27"></path>
            @break
        @case('fish')
            <path d="M9 18c3.2-5.2 9.2-8.2 15.4-6.8 1.8.4 3.2 1.6 3.6 3.4-2.4.6-4.8 0-6.8-1.4"></path>
            <path d="M9 18L4.2 15.2"></path>
            <path d="M9 18l-3.4 4.6"></path>
            <path d="M18.2 11.6L20.8 7.4"></path>
            <circle cx="24.6" cy="12.2" r="0.85" fill="currentColor" stroke="none"></circle>
            <path d="M4 24c2.8 0 2.8-1.6 5.6-1.6S12.4 24 15.2 24s2.8-1.6 5.6-1.6S23.6 24 26.4 24 29.2 22.4 32 22.4"></path>
            @break
        @case('camp')
            <path d="M5 14l7-6 7 6"></path>
            <path d="M7 14v7h10v-7"></path>
            <path d="M22 22h7l-2.4 4.6a2 2 0 0 1-1.8 1.1h-13a2 2 0 0 1-1.8-1.1L7.5 22H22z"></path>
            <path d="M25 22v-5h-3"></path>
            @break
        @case('globe')
            <path d="M16 4a12 12 0 1 0 0 24 12 12 0 0 0 0-24z"></path>
            <path d="M4 16h24"></path>
            <path d="M16 4c3.5 4 3.5 20 0 24"></path>
            <path d="M16 4c-3.5 4-3.5 20 0 24"></path>
            @break
        @case('star')
            <path d="M12 4l2.4 5 5.6.7-4 3.9 1 5.4-5-2.7-5 2.7 1-5.4-4-3.9 5.6-.7z"></path>
            @break
        @case('pin')
            <path d="M12 21s6.5-6.1 6.5-10.4A6.5 6.5 0 0 0 5.5 10.6C5.5 14.9 12 21 12 21z"></path>
            <circle cx="12" cy="10.4" r="2"></circle>
            @break
        @case('headphones')
            <path d="M5 15v-3a7 7 0 0 1 14 0v3"></path>
            <path d="M3.5 14.5h3V19h-3z"></path>
            <path d="M17.5 14.5h3V19h-3z"></path>
            <path d="M17 19a3 3 0 0 1-3 3h-2"></path>
            @break
        @case('shield')
            <path d="M12 3l7 2.5V12c0 4.4-3 7.7-7 9-4-1.3-7-4.6-7-9V5.5z"></path>
            <path d="M8.8 12.2l2.3 2.3 4.1-4.4"></path>
            @break
        @case('search')
            <circle cx="11" cy="11" r="6.5"></circle>
            <path d="M16 16l4.5 4.5"></path>
            @break
        @case('user')
            <circle cx="12" cy="8.5" r="3.6"></circle>
            <path d="M4.8 20c1.3-3.6 4-5.3 7.2-5.3s5.9 1.7 7.2 5.3"></path>
            @break
        @case('arrow')
            <path d="M5 12h13"></path>
            <path d="M12.5 6.5L19 12l-6.5 5.5"></path>
            @break
        @case('nav-grid')
            <path d="M4 4h7v7H4z"></path>
            <path d="M13 4h7v7h-7z"></path>
            <path d="M4 13h7v7H4z"></path>
            <path d="M13 13h7v7h-7z"></path>
            @break
        @case('nav-rod')
            <path d="M5 4c6 .8 9.8 4.5 11.2 10.5"></path>
            <path d="M15.8 14.3l1.2 3"></path>
            <path d="M17 17.3c-1.8.5-3-.3-3.3-1.5"></path>
            <path d="M3 20c2 0 2-1.4 4-1.4S9 20 11 20s2-1.4 4-1.4S17 20 19 20"></path>
            @break
        @case('nav-camp')
            <path d="M4 10.5l8-6 8 6"></path>
            <path d="M6.5 10.5V17h11v-6.5"></path>
            <path d="M9.5 20h11"></path>
            @break
        @case('nav-pin')
            <path d="M12 21s6.5-6.1 6.5-10.4A6.5 6.5 0 0 0 5.5 10.6C5.5 14.9 12 21 12 21z"></path>
            <path d="M14 10.4a2 2 0 1 1-4 0 2 2 0 0 1 4 0z"></path>
            @break
        @case('nav-user')
            <path d="M15.6 8.5a3.6 3.6 0 1 1-7.2 0 3.6 3.6 0 0 1 7.2 0z"></path>
            <path d="M4.8 20c1.3-3.6 4-5.3 7.2-5.3s5.9 1.7 7.2 5.3"></path>
            @break
        @default
            <circle cx="12" cy="12" r="7"></circle>
    @endswitch
</svg>
