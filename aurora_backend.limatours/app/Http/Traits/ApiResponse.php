<?php


namespace App\Http\Traits;


use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

trait ApiResponse
{
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code, $errorDetail = [])
    {
        /* return response()->json(['error' => $message, 'code' => $code], $code); */

        $isDebug = config('app.debug');
        $isProduction = config('app.env') === 'production';

        // ID único del error para correlación en logs
        $errorId = $this->generateErrorId();
        $errorDetail = array_merge(['id' => $errorId], $errorDetail);

        if ($isDebug && !$isProduction && isset($errorDetail['trace']) && is_array($errorDetail['trace'])) {
            $traceResponse = collect($errorDetail['trace'])->take(5)->toArray();
            $errorResponse = array_merge($errorDetail, ['trace' => $traceResponse]);
            $traceLog = json_decode(collect($errorDetail['trace'])->take(5)->toJson());
            $errorLog = array_merge($errorDetail, ['trace' => $traceLog]);
        } else {
            if ($isDebug && (isset($errorDetail['trace']) && is_array($errorDetail['trace']))) {
                $traceLog= json_decode(collect($errorDetail['trace'])->take(5)->toJson());
                $errorLog = array_merge($errorDetail, ['trace' => $traceLog]);
            }

            unset($errorDetail['file'], $errorDetail['line'], $errorDetail['trace']);
        }

        $response = [
            'success' => false,
            'message' => $message,
            'error' => $errorResponse ?? $errorDetail,
            'code' => $code,
            'timestamp' => now()->toDateTimeString(),
        ];

        return response()->json($response, $code);
    }

    public function cacheResponse($data)
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30 / 60, function () use ($data) {
            return $data;
        });
    }

    public function generateErrorId(): string
    {
        $keyError = Str::random();
        return $keyError;
    }
}
