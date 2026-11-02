<?php

namespace App\Models\Concerns;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasListingOwner
{
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creatorName(): ?string
    {
        if ($this->user) {
            $name = trim((string) ($this->user->full_name ?? ''));
            if ($name !== '') {
                return $name;
            }
        }

        $employeeName = trim((string) ($this->employee?->name ?? ''));

        return $employeeName !== '' ? $employeeName : null;
    }

    public function creatorEmail(): ?string
    {
        $email = $this->user?->email ?? $this->employee?->email;

        return filled($email) ? (string) $email : null;
    }
}
