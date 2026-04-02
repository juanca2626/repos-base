<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageItinerary extends Model
{
 
    protected $fillable = ['year', 'link_itinerary_price','link_itinerary_priceless','package_id','language_id'];

    public function language()
    {
        return $this->belongsTo('App\Language');
    }
   
    public function translations()
    {
        return $this->belongsTo('App\PackageTranslation', 'package_id' , 'package_id');
    }
   
}
