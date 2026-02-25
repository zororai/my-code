<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guard($guard)->check()) {
            $landingPage = \App\SchoolSetting::get('landing_page', 'logins');
            
            // Map landing page setting to actual route
            if ($landingPage === 'web') {
                return redirect('/web');
            }
            
            // Default to /home for 'logins' or any other value
            return redirect('/home');
        }

        return $next($request);
    }
}
