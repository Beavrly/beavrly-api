<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criterion extends Model
{
    protected $fillable = ['name', 'description', 'is_global'];

    public function customCriteria()
    {
        return $this->hasMany(CustomCriterion::class);
    }
}
