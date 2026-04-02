<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SerieHeader extends Model
{
    use SoftDeletes;

    protected $table = 'serie_headers';

    protected $fillable = [
        'name',
        'year',
    ];

    public function departures()
    {
        return $this->hasMany(SerieDeparture::class, 'serie_header_id');
    }
}
