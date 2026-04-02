<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class State extends Model
{
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'iso',
        'country_id'
    ];

    public function country()
    {
        return $this->belongsTo('App\Country');
    }

    public function cities()
    {
        return $this->hasMany('App\City');
    }

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'state');
    }

    public function hotels()
    {
        return $this->hasMany('App\Hotel');
    }

    public function scopeStateNameOrCountryName($query, $search,$lang)
    {
        $query->whereHas('translations', function ($query) use ($lang,$search) {
//            $query->where('type', 'state');
            if (trim($search)!="")
            {
                $query->where('value', 'like', '%' . $search . '%');
            }
//            $query->with('language', function ($query) use ($lang) {
//                $query->where('iso', $lang);
//            });
        });
//        $query->whereHas('country', function ($query) use ($lang,$search) {
//            $query->whereHas('translations', function ($query) use ($lang, $search) {
//                $query->where('type', 'country');
//                if (trim($search) != "") {
//                    $query->where('value', 'like', '%' . $search . '%');
//                }
//                $query->whereHas('language', function ($query) use ($lang) {
//                    $query->where('iso', $lang);
//                });
//            });
//        });
    }

    public function galleries()
    {
        return $this->hasMany('App\Galery', 'object_id', 'id')
            ->where('galeries.type', 'state');
    }
}
