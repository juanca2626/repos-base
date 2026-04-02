<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieProgram extends Model
{
    use SoftDeletes;

    protected $table = 'serie_programs';

    protected $fillable = [
        'name',
    ];

    public function departurePrograms()
    {
        return $this->hasMany(SerieDepartureProgram::class, 'serie_program_id');
    }

    // Acceso directo a departures (many-to-many)
    public function departures()
    {
        return $this->belongsToMany(
            SerieDeparture::class,
            'serie_departure_programs',
            'serie_program_id',
            'serie_departure_id'
        )->withPivot(['id', 'date'])->withTimestamps();
    }


}
