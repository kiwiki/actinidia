<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Component extends Model
{

    public function revisions()
    {
        return $this->morphMany(Revision::class, 'revisionable');
    }

    public function author()
    {
        return $this->hasOne(User::class);
    }
}
