<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliciesCancelations extends Model
{
    //use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'hotel_id'
    ];

    public function translations()
    {
        return $this->hasMany('App\Translation', 'object_id', 'id')
            ->where('translations.type', 'cancellationpolicy');
    }

    public function policy_cancellation_parameter()
    {
        return $this->hasMany('App\PolicyCancellationParameter','policy_cancelation_id','id');
    }

    public function policy_rates()
    {
        return $this->hasMany('App\PoliciesRates' , 'policies_cancelation_id' ,'id');
    }
}
