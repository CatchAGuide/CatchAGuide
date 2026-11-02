@props(['user' => null, 'employee' => null, 'listing' => null])

@php
    $subject = $user ?: $employee;
    $fullName = trim((string) ($user?->full_name ?? $user?->name ?? $employee?->name ?? ''));
    $nameParts = $fullName !== '' ? preg_split('/\s+/', $fullName) : [];
    $initials = '';
    if (! empty($nameParts)) {
        $initials .= mb_substr($nameParts[0], 0, 1);
        if (count($nameParts) > 1) {
            $initials .= mb_substr(end($nameParts), 0, 1);
        }
    }
    $photoUrl = $user ? guide_profile_photo_url($user, $listing) : null;
    $isPlaceholder = $photoUrl && str_contains($photoUrl, 'placeholder_guide');
@endphp

@if($subject)
    @if($user)
        <a href="{{ route('admin.guides.edit', $user->id) }}" class="text-decoration-none">
    @endif
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
                @if($user && ($user->information->city ?? null))
                    <small>{{ $user->information->city }}</small>
                @elseif($employee)
                    <small>{{ __('admin.listing_owner.admin') }}</small>
                @endif
            </span>
        </div>
    @if($user)
        </a>
    @endif
@else
    <span class="text-muted">—</span>
@endif
