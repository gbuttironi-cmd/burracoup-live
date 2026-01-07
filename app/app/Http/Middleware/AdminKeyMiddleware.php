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

        // Se non configurata, blocca tutto (fail-closed)
        if ($expected === '') {
            abort(403, 'Admin key not configured.');
        }

        // Accetta key da query (?key=) oppure header X-Admin-Key
        $provided = (string) $request->query('key', $request->header('X-Admin-Key', ''));

        if (!hash_equals($expected, $provided)) {
            abort(403, 'Forbidden.');
        }

        return $next($request);
    }
}
