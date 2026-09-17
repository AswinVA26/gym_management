<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class Ability
{
    /**
     * Ensure the authenticated user satisfies a named ability (gate).
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        abort_unless($request->user() && Gate::allows($ability), 403);

        return $next($request);
    }
}
