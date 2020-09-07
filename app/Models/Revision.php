<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Revision extends Model
{

    public function revisionable()
    {
        return $this->morphTo();
    }
}
