<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComponentRevision extends Model
{

    public function component()
    {
        return $this->belongsTo(Component::class);
    }
}
