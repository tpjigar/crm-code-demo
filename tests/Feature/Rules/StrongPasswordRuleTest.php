<?php

declare(strict_types=1);

use App\Rules\StrongPasswordRule;
use Illuminate\Support\Facades\Validator;

function validateWith(string $password): array
{
    $validator = Validator::make(
        ['password' => $password],
        ['password' => [new StrongPasswordRule]],
    );

    return $validator->errors()->get('password');
}

it('rejects passwords shorter than 12 characters', function (): void {
    $errors = validateWith('Short1!');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('12 characters');
});

it('rejects passwords without uppercase letters', function (): void {
    $errors = validateWith('alllowercase1!');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('uppercase');
});

it('rejects passwords without lowercase letters', function (): void {
    $errors = validateWith('ALLUPPERCASE1!');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('lowercase');
});

it('rejects passwords without a number', function (): void {
    $errors = validateWith('NoNumbersHere!');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('number');
});

it('rejects passwords without a symbol', function (): void {
    $errors = validateWith('NoSymbolsHere1');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('symbol');
});

it('accepts a password that satisfies every rule', function (): void {
    $errors = validateWith('Str0ng-Passw0rd!');

    expect($errors)->toBeEmpty();
});

it('aggregates multiple failures in a single message', function (): void {
    $errors = validateWith('short');

    expect($errors)->not->toBeEmpty()
        ->and($errors[0])->toContain('12 characters')
        ->and($errors[0])->toContain('uppercase')
        ->and($errors[0])->toContain('number')
        ->and($errors[0])->toContain('symbol');
});
