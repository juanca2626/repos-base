<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FrequentQuestions extends Model
{
    use SoftDeletes;

    public function translations_question()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'frequent_question')
            ->where('translations.slug', '=', 'frequentquestion_name');
    }

    public function translations_answer()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', '=', 'frequent_question')
            ->where('translations.slug', '=', 'frequentquestion_answer');
    }

    public function category()
    {
        return $this->belongsTo('App\QuestionCategory', 'question_category_id', 'id');
    }

    public function client_questions()
    {
        return $this->hasMany('App\ClientEcommerceQuestion');
    }

}
