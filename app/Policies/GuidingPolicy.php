<?php

namespace App\Policies;

use App\Models\Guiding;
use App\Models\User;

/**
 * Guide Area tour actions (page and API): only the tour's own guide.
 */
class GuidingPolicy
{
    public function update(User $user, Guiding $guiding): bool
    {
        return (int) $guiding->user_id === (int) $user->id;
    }

    public function duplicate(User $user, Guiding $guiding): bool
    {
        return $this->update($user, $guiding);
    }
}
