<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $casts = [
        'answers' => 'array',
    ];


    public function group()
    {
        return $this->belongsTo(QuestionGroup::class, 'question_group_id', 'id');

    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class, 'question_id', 'id');
    }
}
