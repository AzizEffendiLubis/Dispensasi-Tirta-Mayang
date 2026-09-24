<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        if (! in_array($user->role, $roles)) {
            return redirect($user->dashboardRoute())
                ->with('warning', 'Anda dialihkan karena halaman tersebut bukan untuk akun Anda saat ini.');
        }

        return $next($request);
    }
}