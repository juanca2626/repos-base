<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class QuoteHistoryLog extends Model
{
    use SoftDeletes;

    public function quote()
    {
        return $this->belongsTo('App\Models\Quote');
    }

    public function user()
    {
        return $this->belongsTo('App\Models\User');
    }

}
