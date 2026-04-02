<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Response;

class ServicesApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) {
            if (!auth()->user()) {
                auth()->logout();
            } else {
                return $next($request);
            }
        }

        return $this->response(['message' => 'session_expired','human_read_message' => 'Your session has expired.'],401);
    }

    public function response($data, $code = 200)
    {
        $rsponse = [
            'success' => $code == 200,
            'code' => $code
        ];

        foreach ($data as $key => $datum) {
            $rsponse[$key] = $datum;
        }

        return Response::json($rsponse, $code);
    }

    public function error($message = null, $code = 400)
    {
        // check if $message is object and transforms it into an array
        if (is_object($message)) {
            $message = $message->toArray();
        }

        switch ($code) {
            default:
                $code_message = 'error_occured';
                break;
        }

        $data = array(
            'message' => $code_message,
            'data' => $message
        );

        // return an error
        return $this->response($data, $code);
    }
}
