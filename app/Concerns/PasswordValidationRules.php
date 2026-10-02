<?php

declare(strict_types=1);

namespace App\Concerns;

use App\Rules\StrongPasswordRule;
use Illuminate\Contracts\Validation\ValidationRule;

trait PasswordValidationRules
{
    /**
     * @return array<int, ValidationRule|string>
     */
    protected function passwordRules(): array
    {
        return ['required', 'string', new StrongPasswordRule, 'confirmed'];
    }

    /**
     * @return array<int, ValidationRule|string>
     */
    protected function currentPasswordRules(): array
    {
        return ['required', 'string', 'current_password'];
    }
}
