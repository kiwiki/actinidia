<?php

namespace App\Auth;

use App\Models\User;
use Cog\Laravel\Optimus\Facades\Optimus;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Contracts\Auth\UserProvider;

class KiwikiUserProvider extends EloquentUserProvider implements UserProvider
{
    public function retrieveById($identifier)
    {
        return User::where('id', Optimus::decode($identifier))->first();
    }
}
