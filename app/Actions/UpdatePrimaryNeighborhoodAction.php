<?php

namespace App\Actions;

use App\Models\Neighborhood;
use App\Models\User;

class UpdatePrimaryNeighborhoodAction
{
    public function execute(User $user, Neighborhood $neighborhood): User
    {
        $user->update([
            'neighborhood_id' => $neighborhood->id,
            'neighborhood' => $neighborhood->name,
        ]);

        return $user;
    }
}
