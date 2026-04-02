<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteCategory extends Model
{
    public function type_class()
    {
        return $this->belongsTo('App\TypeClass');
    }
    public function services()
    {
        return $this->hasMany('App\QuoteService');
    }
    public function ranges()
    {
        return $this->hasMany('App\QuoteDynamicSaleRate');
    }
}
