<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Component extends Model
{

    use SoftDeletes;
    use HasFactory;

    public function revisions()
    {
        return $this->hasMany(ComponentRevision::class);
    }

    public function author()
    {
        return $this->hasOne(User::class);
    }
}
