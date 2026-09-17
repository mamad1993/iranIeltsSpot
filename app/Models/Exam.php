<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    public function passages()
    {
        return $this->hasMany(Passage::class, 'exam_id', 'id');

    }
}
