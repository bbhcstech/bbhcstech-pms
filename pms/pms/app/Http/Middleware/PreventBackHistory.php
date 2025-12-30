<?php

namespace App\Http\Middleware;

use Closure;

class PreventBackHistory
{
    /**
     * Handle an incoming request.
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Apply headers only to dashboard or restricted routes
        if ($request->is('dashboard*') || $request->is('verify-otp*')) {
            return $response->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                            ->header('Pragma', 'no-cache')
                            ->header('Expires', '0');
        }

        return $response;
    }
}
