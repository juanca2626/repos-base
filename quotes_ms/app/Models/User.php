<?php

namespace App\Models;

use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;

class User extends Model implements AuthenticatableContract
{
    protected $with = ['client_seller.client'];

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier()
    {
        return $this->id;
    }

    public function getAuthPassword(): null
    {
        return null;
    }

    public function getRememberToken(): null
    {
        return null;
    }

    public function setRememberToken($value)
    {
    }

    public function getRememberTokenName()
    {
    }

    public function client_seller()
    {
        return $this->hasOne('App\Models\ClientSeller');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function clientSellers()
    {
        return $this->belongsToMany('\App\Models\Client', 'client_sellers')
            ->withPivot('client_id', 'id');
    }

}
