<?php

declare(strict_types=1);

it('sends OWASP-recommended security headers on every web response', function (): void {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Content-Security-Policy');

    expect($response->headers->get('Permissions-Policy'))
        ->toContain('camera=()')
        ->toContain('microphone=()');
});

it('does not send HSTS over plain HTTP', function (): void {
    $response = $this->get(route('home'));

    expect($response->headers->has('Strict-Transport-Security'))->toBeFalse();
});

it('includes a Content-Security-Policy with safe defaults', function (): void {
    $response = $this->get(route('home'));

    $csp = $response->headers->get('Content-Security-Policy');

    expect($csp)
        ->toContain("default-src 'self'")
        ->toContain("frame-ancestors 'self'")
        ->toContain("base-uri 'self'")
        ->toContain("form-action 'self'");
});
