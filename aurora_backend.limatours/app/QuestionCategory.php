<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuestionCategory extends Model
{
    use SoftDeletes;

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'question_category');
    }

    public function galleries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id');
    }

    public function frequent_questions()
    {
        return $this->hasMany('App\FrequentQuestions', 'question_category_id', 'id');
    }


}
