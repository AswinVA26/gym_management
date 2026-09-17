<?php

namespace App\Http\Middleware;

use App\Services\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app(TenantManager::class)->ready()) {
            return $next($request);
        }

        $user = $request->user();

        if ($user && $user->isSuperAdmin()) {
            return redirect()->route('platform.index')
                ->with('flash_warning', 'Choose a gym to manage first.');
        }

        abort(403, 'No gym workspace is active for your account.');
    }
}
