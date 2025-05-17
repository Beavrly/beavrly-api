<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomCriterion extends Model
{
    protected $fillable = ['criteria_id', 'project_id', 'scope_id', 'estimative_id', 'name', 'description'];

    public function baseCriterion()
    {
        return $this->belongsTo(Criterion::class, 'criteria_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }

    public function estimative()
    {
        return $this->belongsTo(Estimative::class);
    }
}
