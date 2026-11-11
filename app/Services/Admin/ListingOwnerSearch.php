<?php

namespace App\Services\Admin;

use App\Models\User;

/**
 * Searchable owners for Admin listing forms (providers first, then guides).
 */
class ListingOwnerSearch
{
    /**
     * @return list<array{id: int, label: string, role: string, city: string}>
     */
    public function search(string $term, int $limit = 20): array
    {
        $term = trim($term);
        $limit = max(1, min(50, $limit));

        $query = User::query()
            ->with('information:id,city,company_name')
            ->select(['id', 'firstname', 'lastname', 'email', 'is_guide', 'is_provider', 'guide_status', 'user_information_id']);

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('firstname', 'like', $like)
                    ->orWhere('lastname', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhereRaw("CONCAT(firstname, ' ', lastname) like ?", [$like])
                    ->orWhereHas('information', function ($info) use ($like) {
                        $info->where('company_name', 'like', $like)
                            ->orWhere('city', 'like', $like);
                    });
            });
        } else {
            $query->where(function ($builder) {
                $builder->where('is_provider', true)
                    ->orWhere(function ($guide) {
                        $guide->whereVerifiedGuide();
                    });
            });
        }

        return $query
            ->orderByDesc('is_provider')
            ->orderBy('firstname')
            ->orderBy('lastname')
            ->limit($limit)
            ->get()
            ->map(fn (User $user) => $this->present($user))
            ->values()
            ->all();
    }

    /**
     * @return array{id: int, label: string, role: string, city: string}|null
     */
    public function presentOne(?User $user): ?array
    {
        return $user ? $this->present($user) : null;
    }

    /**
     * @return array{id: int, label: string, role: string, city: string}
     */
    private function present(User $user): array
    {
        $company = trim((string) ($user->information?->company_name ?? ''));
        $name = trim($user->firstname.' '.$user->lastname);
        $label = $company !== '' ? $company : ($name !== '' ? $name : ('#'.$user->id));
        if ($user->email) {
            $label .= ' · '.$user->email;
        }

        return [
            'id' => (int) $user->id,
            'label' => $label,
            'role' => $user->isVerifiedGuide() ? 'guide' : ($user->isProvider() ? 'provider' : 'customer'),
            'city' => trim((string) ($user->information?->city ?? '')),
        ];
    }
}
