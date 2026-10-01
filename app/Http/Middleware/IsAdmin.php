<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user
            || (! $user->isSuperAdmin()
                && ! $user->hasRole('Administrador de Banda')
                && ! $user->hasRole('admin')
                && ! $user->is_admin)
        ) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
