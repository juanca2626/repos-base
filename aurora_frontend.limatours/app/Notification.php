<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    //
    protected $fillable = [
        'title', 'content', 'target', 'type', 'url', 'user', 'status', 'data', 'created_by', 'updated_by',
        'module', 'created_at', 'updated_at'
    ];

    public function user()
    {
        return $this->belongsToMany('App\User', 'user', 'user', 'code', '', 'code');
    }
}
