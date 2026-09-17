<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Apply baseline hardening headers to every response.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), interest-cohort=()');
        $response->headers->set('X-XSS-Protection', '0');

        if (in_array((string) config('app.env'), ['production', 'prod'], true)) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        $response->headers->set('Content-Security-Policy', $this->cspPolicy());

        return $response;
    }

    protected function cspPolicy(): string
    {
        $scripts = "'self' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
        $styles = "'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
        $connect = "'self' http://localhost:11434";

        if (! app()->isProduction()) {
            // Allow Vite dev-server HMR + its inline preamble.
            $scripts .= " 'unsafe-inline'";
            $connect .= ' ws://localhost:* http://localhost:* ws://127.0.0.1:* http://127.0.0.1:*';
        }

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scripts}",
            "style-src {$styles}",
            "img-src 'self' data: https:",
            "font-src 'self' data: https://cdnjs.cloudflare.com",
            "connect-src {$connect}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ]);
    }
}
