<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieDepartureProgram extends Model
{
    use softDeletes;

    protected $table = 'serie_departure_programs';

    protected $fillable = [
        'serie_program_id',
        'serie_departure_id',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function program()
    {
        return $this->belongsTo(SerieProgram::class, 'serie_program_id');
    }

    public function departure()
    {
        return $this->belongsTo(SerieDeparture::class, 'serie_departure_id');
    }

    public function trackingControls()
    {
        return $this->hasMany(SerieTrackingControl::class, 'serie_departure_program_id');
    }
}
