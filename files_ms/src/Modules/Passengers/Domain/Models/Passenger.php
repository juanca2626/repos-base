<?php

namespace Src\Modules\Passengers\Domain\Models;
  
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passenger extends Model
{
    use SoftDeletes;

    protected $table = 'file_passengers';

    protected $fillable = [
        'file_id',
        'name',
        'surnames',
        'document_type_id',
        'document_number',
        'date_birth',
        'type',
        'genre',
        'email',
        'phone',
        'country_iso'
    ];


    public function setTypeAttribute($value)
    {
        $this->attributes['type'] = strtoupper($value);
    }

    public function setGenreAttribute($value)
    {
        $this->attributes['genre'] = strtoupper($value);
    }
}