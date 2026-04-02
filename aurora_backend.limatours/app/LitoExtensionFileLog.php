<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LitoExtensionFileLog extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'lito_extension_file_id', 'user_id', 'action',
    ];

    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
