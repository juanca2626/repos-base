<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    /*
     * Todo Column: status
     *  Estados dentro de una cotizacion
     */

    // Todo Cotizacion en modo edicion (Borrador)
    const STATUS_EDITING_DRAFT = 2;

    // Todo Cotizacion cerrada
    const STATUS_CLOSE = 1;

    // Todo Cotizacion eliminada
    const STATUS_DELETE = 0;



    protected $fillable = ['shared'];

    public function history_logs()
    {
        return $this->hasMany('App\QuoteHistoryLog');
    }

    public function logs()
    {
        return $this->hasMany('App\QuoteLog');
    }

    public function log_user()
    {
        return $this->hasMany('App\QuoteLog')
            ->whereIn('quote_logs.type', ['copy_to', 'copy_from']);
    }

    public function categories()
    {
        return $this->hasMany('App\QuoteCategory');
    }

    public function distributions()
    {
        return $this->hasMany('App\QuoteDistribution');
    }
        

    public function ranges()
    {
        return $this->hasMany('App\QuoteRange');
    }

    public function notes()
    {
        return $this->hasMany('App\QuoteNote');
    }

    public function passengers()
    {
        return $this->hasMany('App\QuotePassenger');
    }

    public function people()
    {
        return $this->hasMany('App\QuotePeople');
    }

    public function destinations()
    {
        return $this->hasMany('App\QuoteDestination');
    }

    public function service_type()
    {
        return $this->belongsTo('App\ServiceType');
    }

    public function age_child()
    {
        return $this->hasMany('App\QuoteAgeChild');
    }

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function permission()
    {
        return $this->hasOne('App\ShareQuote', 'quote_id', 'id');
    }

    public function reservation()
    {
        return $this->hasOne('App\Reservation', 'object_id', 'id')
            ->where('reservations.entity','Quote')
            ->whereNotNull('reservations.type_class_id')
            ->orderBy('reservations.id', 'desc');
    }

    public function accommodation()
    {
        return $this->hasOne('App\QuoteAccommodation');
    }

}
