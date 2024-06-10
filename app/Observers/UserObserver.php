<?php

namespace App\Observers;

use App\Models\User;
use App\Models\UserInformation;

class UserObserver
{
    public function creating(User $user)
    {
        $user_information = UserInformation::create([]);

        $user->user_information_id = $user_information->id;
    }
}
