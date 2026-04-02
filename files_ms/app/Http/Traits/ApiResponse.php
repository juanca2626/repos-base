<?php

namespace App\Http\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

trait ApiResponse
{
    private function successResponse($code, $data = null, $success = true): \Illuminate\Http\JsonResponse
    {

        // Agrega esta lógica para manejar la paginación
        if ($data instanceof LengthAwarePaginator) {
            return response()->json([
                'success' => $success,
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ],
                'code' => $code
            ], $code);
        } else {
            return response()->json(['success' => $success, 'data' => $data, 'code' => $code], $code);
        }

    }

    protected function errorResponse($message, $code, $success = false): \Illuminate\Http\JsonResponse
    {
        return response()->json(['success' => $success, 'error' => $message, 'code' => $code], $code);
    }
}
