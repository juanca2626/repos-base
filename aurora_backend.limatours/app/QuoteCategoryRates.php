<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QuoteCategoryRates extends Model
{
    protected $fillable = ['quote_category_id', 'type', 'optional' ,'total_price'];  
}
