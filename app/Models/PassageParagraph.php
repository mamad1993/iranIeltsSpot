<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PassageParagraph extends Model
{
    public function passage()
    {
        return $this->belongsTo(Passage::class, 'passage_id', 'id');
    }
}
