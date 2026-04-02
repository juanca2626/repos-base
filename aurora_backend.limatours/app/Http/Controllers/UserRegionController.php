<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class UserRegionController extends Controller
{
    /**
     * Obtener las regiones de negocio asociadas a un usuario
     *
     * @param  int  $userId
     * @return \Illuminate\Http\Response
     */
    public function getUserRegions($userId)
    {
        try {
            $user = User::with('businessRegions')->findOrFail($userId);
            $regions = $user->businessRegions;

            return response()->json([
                'success' => true,
                'data' => $regions,
                'code' => 200
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
                'code' => 404
            ], 404);
        }
    }
}
