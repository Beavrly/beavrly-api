<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimative extends Model
{
    protected $table = 'estimatives';

    protected $guarded = [];

    protected $casts = [
        'additional_context' => 'array',
        'structured_risks' => 'array',
    ];

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }


}
