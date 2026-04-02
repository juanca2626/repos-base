<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteNote extends Model
{
    protected $table = 'quote_notes';

    public function user(){
        return $this->belongsTo('App\User');
    }
}
