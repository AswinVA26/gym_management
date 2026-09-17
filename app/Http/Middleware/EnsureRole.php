<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simple role gate. Super admins may use gym-level roles (they can inspect
 * any gym), but only actual super admins may access platform-only roles.
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(403);
        }

        $roles = array_values(array_filter($roles));

        if ($user->isSuperAdmin()) {
            $hasGymRole = ! empty(array_intersect($roles, ['gym_admin', 'trainer', 'member']));

            if (! $hasGymRole && ! in_array('super_admin', $roles, true)) {
                abort(403);
            }
        } elseif (! in_array($user->role, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
