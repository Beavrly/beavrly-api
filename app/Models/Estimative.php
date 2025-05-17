<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estimative extends Model
{
    protected $table = 'estimatives';

    protected $fillable = [
        'scope_id',
        'project_id',
        'content',
        'type',
        'estimated_hours_optimistic',
        'total_value_optimistic',
        'estimated_hours_pessimistic',
        'total_value_pessimistic',
        'estimated_hours_average',
        'total_value_average',
        'status',
        'approval',
        'additional_context',
        'source_file',
        'considerations',
    ];

    protected $casts = [
        'additional_context' => 'array',
        'structured_risks' => 'array',
    ];

    public function scope()
    {
        return $this->belongsTo(Scope::class);
    }


}
