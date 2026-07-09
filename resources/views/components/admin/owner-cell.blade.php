@props(['user'])

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
@endphp

@if($user)
    <a href="{{ route('admin.guides.edit', $user->id) }}" class="text-decoration-none">
        <div class="admin-listing-owner-cell">
            @if(!empty($user->profil_image ?? null))
                <img
                    src="{{ asset('uploads/profile_images/' . $user->profil_image) }}"
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
