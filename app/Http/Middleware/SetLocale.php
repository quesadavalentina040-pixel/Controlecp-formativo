<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that sets the application locale from the session.
 * Registered under the alias "lang" in bootstrap/app.php.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('lang')) {
            app()->setLocale(session('lang'));
        }

        return $next($request);
    }
}
