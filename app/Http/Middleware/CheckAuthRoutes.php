<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAuthRoutes
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('login')) {

            if ($request->host() !== config('app.domain')) {
                return redirect()->away(config('app.url').'/login');
            }
        }

        return $next($request);
    }
}
