<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }
    
   public function handle(Request $request, Closure $next, $role)
    {
        // Check if the user has the correct role
        if (auth()->check() && auth()->user()->role == $role) {
            return $next($request);
        }

        // If the role doesn't match, redirect to home or error page
        return redirect('home')->with('error', 'You do not have the required role.');
    }

}
