<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scope extends Model
{
    protected $guarded = [];


    public function transcript()
    {
        return $this->belongsTo(Transcription::class, 'transcript_id', 'id');
    }

    public function estimatives()
    {
        return $this->hasMany(Estimative::class);
    }



}
