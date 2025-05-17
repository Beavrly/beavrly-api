<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    protected $table = 'transcripts'; 

    protected $guarded = [];


    public function scope()
    {
        return $this->hasOne(Scope::class, 'transcription_id', 'id');
    }


}
