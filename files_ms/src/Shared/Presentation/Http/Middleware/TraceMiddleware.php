<?php

namespace Src\Shared\Presentation\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Src\Shared\Logging\TraceContext;

class TraceMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $traceId = $request->header('X-Trace-Id');

        if ($traceId) {
            TraceContext::set($traceId);
        } else {
            TraceContext::generate();
        }

        $response = $next($request);
                
        // agregar trace_id al response header
        $response->headers->set('X-Trace-Id', TraceContext::get());

        return $response;

    }
}