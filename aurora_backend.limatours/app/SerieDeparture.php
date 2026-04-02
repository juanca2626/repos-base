<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieDeparture extends Model
{
    use SoftDeletes;

    protected $table = 'serie_departures';

    protected $fillable = [
        'serie_header_id',
        'name',
        'has_holiday',
        'name_holiday',
        'has_extra_departure',
        'link_guidelines',
    ];

    protected $casts = [
        'has_holiday' => 'boolean',
    ];

    public function header()
    {
        return $this->belongsTo(SerieHeader::class, 'serie_header_id');
    }

    public function departurePrograms()
    {
        return $this->hasMany(SerieDepartureProgram::class, 'serie_departure_id');
    }

    // Si quieres acceder directo a los programs (many-to-many via tabla pivote)
    public function programs()
    {
        return $this->belongsToMany(
            SerieProgram::class,
            'serie_departure_programs',
            'serie_departure_id',
            'serie_program_id'
        )->withPivot(['id', 'date'])->withTimestamps();
    }
}
