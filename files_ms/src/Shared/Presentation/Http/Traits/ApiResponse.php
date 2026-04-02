<?php

namespace Src\Shared\Presentation\Http\Traits;

use Illuminate\Pagination\LengthAwarePaginator;
use Src\Shared\Logging\TraceContext;

trait ApiResponse
{
    protected function successResponse(
        int $code,
        mixed $data = null
    ) : \Illuminate\Http\JsonResponse {
        if ($data instanceof LengthAwarePaginator) {
            return response()->json([
                'success' => true,
                'trace_id' => TraceContext::get(),
                'data' => $data->items(),
                'meta' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                ]
            ], $code);
        }

        return response()->json([
            'success' => true,
            'trace_id' => TraceContext::get(),
            'data' => $data
        ], $code);
    }

    
    protected function errorResponse(
        string $message,
        int $code,
        ?string $errorCode = null,
        array $errors = []
    ) : \Illuminate\Http\JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error_code' => $errorCode,
            'trace_id' => TraceContext::get(),
            'errors' => $errors
        ], $code);
    }

}