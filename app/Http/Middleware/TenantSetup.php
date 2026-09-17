<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves and activates the tenant database for the authenticated user.
 *
 * Database-per-tenant isolation: each request is pinned to exactly one gym
 * database. A suspended gym immediately locks its staff out.
 */
class TenantSetup
{
    public function __construct(
        protected TenantManager $manager,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $next($request);
        }

        if ($user->isSuperAdmin()) {
            $this->applySuperAdminContext($request);

            return $next($request);
        }

        if ($user->belongsToTenant()) {
            $tenant = $user->tenant;
            $tenant = $tenant instanceof Tenant ? $tenant : Tenant::find($user->tenant_id);

            if ($tenant === null || $tenant->isSuspended()) {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                abort(403, 'Your gym account is no longer active.');
            }

            $this->manager->set($tenant);
        }

        return $next($request);
    }

    /**
     * A platform super admin may opt to inspect a specific gym (via session
     * override) without leaving the platform.
     */
    protected function applySuperAdminContext(Request $request): void
    {
        $overrideId = (int) $request->session()->get('tenant_override');

        if ($overrideId <= 0) {
            return;
        }

        $tenant = Tenant::find($overrideId);

        if ($tenant === null || $tenant->isSuspended()) {
            $request->session()->forget('tenant_override');

            return;
        }

        $this->manager->set($tenant);
    }
}
