<?php

declare(strict_types=1);

namespace App\Services\Security;

/**
 * Masks personally identifiable information (PII) for display in UI
 * contexts where the viewer should not see the raw value — e.g., a
 * client-role user looking at a contact list, where exposing emails
 * invites credential-stuffing and phishing against the client's own
 * staff.
 *
 * The policy is deterministic (no randomness) so the same input always
 * produces the same mask: this makes screenshots and audit replays
 * reproducible, and prevents the mask itself from leaking information
 * through variation.
 */
final class SensitiveDataMasker
{
    public function email(?string $email): ?string
    {
        if ($email === null || $email === '') {
            return $email;
        }

        $parts = explode('@', $email, 2);
        if (count($parts) !== 2) {
            return str_repeat('*', mb_strlen($email));
        }

        [$local, $domain] = $parts;

        $localMasked = $this->maskKeepingEdges($local, keepHead: 1, keepTail: 1);

        $domainParts = explode('.', $domain);
        $tld = array_pop($domainParts);
        $domainBody = implode('.', $domainParts);
        $domainMasked = $this->maskKeepingEdges($domainBody, keepHead: 1, keepTail: 0).'.'.$tld;

        return "{$localMasked}@{$domainMasked}";
    }

    public function phone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return $phone;
        }

        $digits = preg_replace('/\D/', '', $phone) ?? '';

        if (mb_strlen($digits) <= 4) {
            return str_repeat('*', mb_strlen($digits));
        }

        $visible = mb_substr($digits, -4);
        $masked = str_repeat('*', mb_strlen($digits) - 4);

        return "{$masked}{$visible}";
    }

    private function maskKeepingEdges(string $value, int $keepHead, int $keepTail): string
    {
        $length = mb_strlen($value);

        if ($length <= $keepHead + $keepTail) {
            return str_repeat('*', max(1, $length));
        }

        $head = mb_substr($value, 0, $keepHead);
        $tail = $keepTail > 0 ? mb_substr($value, -$keepTail) : '';
        $maskedCount = $length - $keepHead - $keepTail;

        return $head.str_repeat('*', $maskedCount).$tail;
    }
}
