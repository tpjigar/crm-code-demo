<?php

declare(strict_types=1);

namespace App\DTOs;

final readonly class ContactData
{
    public function __construct(
        public int $clientId,
        public string $firstName,
        public string $lastName,
        public ?string $jobTitle,
        public ?string $email,
        public ?string $phone,
        public bool $isPrimary,
        public ?string $notes,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            clientId: (int) $validated['client_id'],
            firstName: (string) $validated['first_name'],
            lastName: (string) $validated['last_name'],
            jobTitle: self::nullableString($validated, 'job_title'),
            email: self::nullableString($validated, 'email'),
            phone: self::nullableString($validated, 'phone'),
            isPrimary: (bool) ($validated['is_primary'] ?? false),
            notes: self::nullableString($validated, 'notes'),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'client_id' => $this->clientId,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'job_title' => $this->jobTitle,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_primary' => $this->isPrimary,
            'notes' => $this->notes,
        ];
    }

    /**
     * @param  array<string, mixed>  $source
     */
    private static function nullableString(array $source, string $key): ?string
    {
        $value = $source[$key] ?? null;

        return $value === null ? null : (string) $value;
    }
}
