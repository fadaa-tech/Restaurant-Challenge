<?php

namespace App\Services\Auth;

use App\Models\User;

class UserCreationService
{
   public function createUser($data)
   {
    $user = User::create($data);
    return $user;
   }
}
