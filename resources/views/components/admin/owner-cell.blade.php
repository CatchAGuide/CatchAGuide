@props(['user', 'listing' => null])

@php
    $fullName = $user->full_name ?? $user->name ?? '';
    $nameParts = preg_split('/\s+/', trim($fullName));
    $initials = '';
    if (!empty($nameParts)) {
        $initials .= mb_substr($nameParts[0], 0, 1);
        if (count($nameParts) > 1) {
            $initials .= mb_substr(end($nameParts), 0, 1);
        }
    }
    $photoUrl = $user ? guide_profile_photo_url($user, $listing) : null;
    $isPlaceholder = $photoUrl && str_contains($photoUrl, 'placeholder_guide');
@endphp

@if($user)
    <a href="{{ route('admin.guides.edit', $user->id) }}" class="text-decoration-none">
        <div class="admin-listing-owner-cell">
            @if($photoUrl && ! $isPlaceholder)
                <img
                    src="{{ $photoUrl }}"
                    alt="{{ $fullName }}"
                    class="admin-listing-owner-avatar"
                    loading="lazy"
                    decoding="async"
                >
            @else
                <span class="admin-listing-owner-avatar-placeholder">
                    {{ $initials ?: '?' }}
                </span>
            @endif
            <span class="admin-listing-owner-name">
                {{ $fullName ?: 'Unknown' }}
                @if($user->information->city ?? null)
                    <small>{{ $user->information->city }}</small>
                @endif
            </span>
        </div>
    </a>
@else
    <span class="text-muted">—</span>
@endif
