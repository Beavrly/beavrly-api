<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimative extends Model
{
    protected $table = 'estimatives';

    protected $guarded = [];

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }


}
