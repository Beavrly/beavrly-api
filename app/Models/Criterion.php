<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterion extends Model
{
    protected $guarded = [];


    public function customCriteria()
    {
        return $this->hasMany(CustomCriterion::class);
    }
}
