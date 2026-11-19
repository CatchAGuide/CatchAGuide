<?php

namespace App\Policies;

use App\Models\GuideCertificate;
use App\Models\User;

class GuideCertificatePolicy
{
    public function view(User $user, GuideCertificate $certificate): bool
    {
        return $certificate->user_id === $user->id;
    }

    public function update(User $user, GuideCertificate $certificate): bool
    {
        return $certificate->user_id === $user->id;
    }
}
