<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class SerieTrackingControl extends Model implements Auditable
{
    use SoftDeletes, \OwenIt\Auditing\Auditable;

    public function generateTags(): array
    {
        return ['series.facile'];
    }

    protected $table = 'serie_tracking_controls';

    protected $fillable = [
        'serie_departure_program_id',
        'file',
        'passenger_group_name',
        'qty_passengers',
        'client_id',
        'user_id',
        'ticket_mapi',
        'observation',
    ];

    public function departureProgram()
    {
        return $this->belongsTo(SerieDepartureProgram::class, 'serie_departure_program_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
