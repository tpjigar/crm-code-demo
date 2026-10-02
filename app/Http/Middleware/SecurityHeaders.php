<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * OWASP-recommended HTTP response headers.
     *
     * Vite dev server injects inline scripts, so CSP is relaxed to
     * 'unsafe-inline' in local only. In production, scripts are built
     * and hashed by Vite — tighten CSP there.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $headers = [
            'X-Frame-Options' => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Content-Security-Policy' => $this->buildCsp(),
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    private function buildCsp(): string
    {
        $isLocal = app()->environment('local');

        $scriptSrc = $isLocal
            ? "'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:*"
            : "'self'";

        $connectSrc = $isLocal
            ? "'self' ws://localhost:* http://localhost:* http://127.0.0.1:*"
            : "'self'";

        return implode('; ', [
            "default-src 'self'",
            "script-src {$scriptSrc}",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: blob:",
            "font-src 'self' data:",
            "connect-src {$connectSrc}",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
        ]);
    }
}
