<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    protected $table = 'scopes';

    protected $fillable = [
        'content',
        'source_file',
        'transcript_id',
        'project_id',
        'type',
        'approval'
    ];

    public function transcript()
    {
        return $this->belongsTo(Transcription::class, 'transcript_id', 'id');
    }

    public function estimatives()
    {
        return $this->hasMany(Estimative::class);
    }
}
