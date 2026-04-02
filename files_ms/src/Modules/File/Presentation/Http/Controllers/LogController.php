<?php

namespace Src\Modules\File\Presentation\Http\Controllers;

use App\Http\Controllers\Controller;

class LogController extends Controller{

    public function downloadLog($date = null){
        $filename = $date ? "laravel-{$date}.log" : 'laravel.log';
        $logPath = storage_path("logs/{$filename}");

        if (!file_exists($logPath)) {
            $logFiles = glob(storage_path('logs/laravel-*.log'));
            $dates = array_map(function($path) {
                return basename($path, '.log');
            }, $logFiles);

            return response()->json(['available_logs' => $dates]);
            // return response()->json(['error' => 'Log file not found'], 404);
        }

        return response()->download($logPath, $filename);
    }
}
