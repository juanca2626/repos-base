<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use App\User;
class ServiceAuthController extends Controller
{
    public function __construct()
    {

    }
  
    public function serviceToken(Request $request)
    {
        if (
            $request->client_id !== config('services.files_service.client_id') ||
            $request->client_secret !== config('services.files_service.client_secret')
        ) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $serviceUser = User::where('email', config('services.files_service.email'))->first();

        if (!$serviceUser) {
            return response()->json(['error' => 'Service user not found'], 500);
        }

        $token = JWTAuth::fromUser($serviceUser);

        return response()->json([
            'access_token' => $token,
            'expires_in' => config('jwt.ttl') * 60
        ]);
    }

 
}