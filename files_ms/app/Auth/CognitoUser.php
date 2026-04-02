<?php
namespace App\Auth;

use Illuminate\Contracts\Auth\Authenticatable;

class CognitoUser implements Authenticatable
{
    protected array $attributes;

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function getAuthIdentifierName()
    {
        return 'id'; // o el campo que uses como ID único
    }

    public function getAuthIdentifier()
    {
        return $this->attributes['id'];
    }

    public function getAuthPassword() { return null; }

    public function getRememberToken() { return null; }
    public function setRememberToken($value) {}
    public function getRememberTokenName() { return null; }

    public function __get($key)
    {
        return $this->attributes[$key] ?? null;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}