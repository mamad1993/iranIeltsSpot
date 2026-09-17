<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Passage extends Model
{
    public function paragraphs()
    {
        return $this->hasMany(PassageParagraph::class,'passage_id', 'id');

    }

    public function exam()
    {
        return $this->belongsTo(Exam::class, 'exam_id', 'id');
    }

    public function groups()
    {
        return $this->hasMany(QuestionGroup::class, 'passage_id', 'id');
    }
}
