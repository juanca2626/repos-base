<?php

namespace App\Guard;

use App\Models\User;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Tymon\JWTAuth\JWT;

class JWTGuard implements Guard
{
    use GuardHelpers;

    protected JWT $jwt;

    protected Request $request;

    /**
     * JWTGuard constructor.
     */
    public function __construct(JWT $jwt, Request $request)
    {
        $this->jwt = $jwt;
        $this->request = $request;
    }

    public function user(): User|Authenticatable|null
    {
        if (! is_null($this->user)) {
            return $this->user;
        }
        if ($this->jwt->setRequest($this->request)->getToken() && $this->jwt->check()) {
            $id = $this->jwt->payload()->get('sub');

            $this->user = User::find($id);

            return $this->user;
        }

        return null;
    }

    public function validate(array $credentials = [])
    {
    }
}
