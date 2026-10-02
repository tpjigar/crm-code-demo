<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPasswordRule implements ValidationRule
{
    private const MIN_LENGTH = 12;

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('validation.string')->translate();

            return;
        }

        $failures = [];

        if (mb_strlen($value) < self::MIN_LENGTH) {
            $failures[] = sprintf('at least %d characters', self::MIN_LENGTH);
        }

        if (preg_match('/[A-Z]/', $value) !== 1) {
            $failures[] = 'one uppercase letter';
        }

        if (preg_match('/[a-z]/', $value) !== 1) {
            $failures[] = 'one lowercase letter';
        }

        if (preg_match('/\d/', $value) !== 1) {
            $failures[] = 'one number';
        }

        if (preg_match('/[^A-Za-z0-9]/', $value) !== 1) {
            $failures[] = 'one symbol';
        }

        if ($failures !== []) {
            $fail(sprintf(
                'The :attribute must contain %s.',
                $this->joinNaturally($failures),
            ));
        }
    }

    /**
     * @param  list<string>  $items
     */
    private function joinNaturally(array $items): string
    {
        if (count($items) === 1) {
            return $items[0];
        }

        $last = array_pop($items);

        return implode(', ', $items).' and '.$last;
    }
}
