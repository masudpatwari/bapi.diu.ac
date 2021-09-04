<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RestrictIpAddressMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $myArrayValue = explode(',', env('restrictedIp'));

        if (!in_array($request->ip(), $myArrayValue)) {
            return response()->json(['message' => "You are not allowed to access this site."]);
        }

        return $next($request);
    }
}
