<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'name',
    ];

    public function country(): BelongsTo
    {
        return $this->belongsTo('App\Models\Country');
    }

    public function cities(): HasMany
    {
        return $this->hasMany('App\Models\City');
    }

    public function translations(): HasMany
    {
        return $this->hasMany('App\Models\Translation', 'object_id', 'id')
            ->where('translations.type', 'state');
    }

    public function hotels(): HasMany
    {
        return $this->hasMany('App\Models\Hotel');
    }

    public function scopeStateNameOrCountryName($query, $search, $lang): void
    {
        $query->whereHas('translations', function ($query) use ($search) {
            //            $query->where('type', 'state');
            if (trim($search) != '') {
                $query->where('value', 'like', '%'.$search.'%');
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

    public function galleries(): HasMany
    {
        return $this->hasMany('App\Models\Galery', 'object_id', 'id')
            ->where('galeries.type', 'state');
    }
}
