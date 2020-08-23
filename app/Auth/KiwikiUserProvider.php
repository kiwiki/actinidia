<?php

namespace App\Auth;

use App\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;

class KiwikiUserProvider extends EloquentUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return User::where('username', $identifier)->first();
    }
}
