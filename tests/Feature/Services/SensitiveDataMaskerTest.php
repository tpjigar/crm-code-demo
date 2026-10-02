<?php

declare(strict_types=1);

use App\Services\Security\SensitiveDataMasker;

beforeEach(function (): void {
    $this->masker = new SensitiveDataMasker;
});

it('passes null and empty strings through unchanged', function (): void {
    expect($this->masker->email(null))->toBeNull()
        ->and($this->masker->email(''))->toBe('')
        ->and($this->masker->phone(null))->toBeNull()
        ->and($this->masker->phone(''))->toBe('');
});

it('masks an email keeping first and last of the local part + first of the domain + tld', function (): void {
    expect($this->masker->email('jane.doe@acme-corp.com'))
        ->toBe('j******e@a********.com');
});

it('masks a malformed email entirely', function (): void {
    expect($this->masker->email('not-an-email'))->toBe('************');
});

it('masks a phone keeping only the last four digits', function (): void {
    expect($this->masker->phone('+1-555-867-5309'))->toBe('*******5309');
});

it('masks every digit for a very short phone', function (): void {
    expect($this->masker->phone('123'))->toBe('***');
});

it('produces identical masks for identical inputs', function (): void {
    $a = $this->masker->email('pat@example.com');
    $b = $this->masker->email('pat@example.com');

    expect($a)->toBe($b);
});
