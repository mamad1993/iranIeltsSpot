<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestionGroup extends Model
{
    public function passage()
    {
        return $this->belongsTo(Passage::class, 'passage_id', 'id');

    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_group_id', 'id');
    }

    public function questions()
    {
        return $this->hasMany(Question::class, 'question_group_id', 'id');

    }
}
