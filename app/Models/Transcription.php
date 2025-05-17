<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    protected $table = 'transcripts'; 

    protected $fillable = ['content', 'source_file', 'status', 'type', 'project_id'];


    public function scope()
    {
        return $this->hasOne(Scope::class, 'transcription_id', 'id');
    }


}
