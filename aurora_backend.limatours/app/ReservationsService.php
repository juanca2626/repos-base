<?php

namespace App;

use App\CustomModel as Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReservationsService extends Model
{
    use SoftDeletes;

    /*
     * Campo: status_email
     */
    const STATUS_EMAIL_SENT = 1;
    const STATUS_EMAIL_NOT_SENT = 0;

    /*
     * Campo: status_stela_reserve
     */
    const STATUS_STELA_RESERVE_SENT = 1;
    const STATUS_STELA_RESERVE_NOT_SENT = 0;


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reservation()
    {
        return $this->belongsTo(Reservation::class);
    }




    public function reservationsServiceRatesPlans()
    {
        return $this->hasMany(ReservationsServicesRatesPlans::class);
    }

    public function service()
    {
        return $this->belongsTo('App\Service', 'service_id', 'id');
    }
}
