<?php

declare(strict_types=1);

namespace App\Services\Security;

/**
 * Minimal user-agent summarizer for display in the session manager.
 *
 * A full UA parser is overkill here and pulls a sizable regex database;
 * we only need enough signal for an admin to recognize their own device
 * ("Chrome on macOS", "Safari on iPhone") and spot something anomalous
 * ("curl/8.4", "python-requests/2.31").
 */
final class UserAgentParser
{
    /**
     * @return array{browser: string, os: string, device: string}
     */
    public function parse(?string $userAgent): array
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return ['browser' => 'Unknown', 'os' => 'Unknown', 'device' => 'Unknown'];
        }

        return [
            'browser' => $this->browser($userAgent),
            'os' => $this->os($userAgent),
            'device' => $this->device($userAgent),
        ];
    }

    private function browser(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Edg/') => 'Edge',
            str_contains($ua, 'OPR/') || str_contains($ua, 'Opera') => 'Opera',
            str_contains($ua, 'Firefox/') => 'Firefox',
            str_contains($ua, 'Chrome/') && ! str_contains($ua, 'Chromium') => 'Chrome',
            str_contains($ua, 'Chromium') => 'Chromium',
            str_contains($ua, 'Safari/') => 'Safari',
            str_starts_with($ua, 'curl/') => 'curl',
            str_contains($ua, 'python-requests') => 'python-requests',
            str_contains($ua, 'Go-http-client') => 'Go HTTP client',
            default => 'Other',
        };
    }

    private function os(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'Windows NT 10') => 'Windows 10/11',
            str_contains($ua, 'Windows NT') => 'Windows',
            str_contains($ua, 'Mac OS X') || str_contains($ua, 'macOS') => 'macOS',
            str_contains($ua, 'iPhone') => 'iOS',
            str_contains($ua, 'iPad') => 'iPadOS',
            str_contains($ua, 'Android') => 'Android',
            str_contains($ua, 'Linux') => 'Linux',
            default => 'Unknown',
        };
    }

    private function device(string $ua): string
    {
        return match (true) {
            str_contains($ua, 'iPhone') || str_contains($ua, 'Android') && str_contains($ua, 'Mobile') => 'Phone',
            str_contains($ua, 'iPad') || str_contains($ua, 'Tablet') => 'Tablet',
            str_starts_with($ua, 'curl/') || str_contains($ua, 'python-requests') => 'Script',
            default => 'Desktop',
        };
    }
}
