<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user && $user->is_active && in_array($user->role, $roles, true), 403);

        if ($user->role === 'seller') {
            abort_unless($user->seller()->exists(), 403);
        }

        return $next($request);
    }
}
