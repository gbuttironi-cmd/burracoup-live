<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminKeyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('app.admin_key', env('ADMIN_KEY', ''));

        if ($expected === '') {
            abort(403, 'Admin key not configured.');
        }

        // accetta key oppure admin_key, oppure header
        $provided = (string) (
            $request->query('key')
            ?: $request->query('admin_key')
            ?: $request->header('X-Admin-Key')
            ?: $request->header('X-ADMIN-KEY')
            ?: ''
        );

        if (!hash_equals($expected, $provided)) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}