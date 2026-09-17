<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionOption extends Model
{
    public function group()
    {
        return $this->belongsTo(QuestionGroup::class, 'question_group_id', 'id');

    }

    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id', 'id');

    }
}
