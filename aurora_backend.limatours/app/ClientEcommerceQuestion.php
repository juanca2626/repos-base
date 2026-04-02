<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientEcommerceQuestion extends Model
{
    use SoftDeletes;

    public function question()
    {
        return $this->belongsTo('App\FrequentQuestions', 'frequent_questions_id', 'id');
    }
}
