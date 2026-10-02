<?php

declare(strict_types=1);

namespace App\DTOs;

/**
 * Typed, immutable data carrier between the HTTP layer (FormRequests)
 * and the service / action layer. Service methods accept this instead
 * of the HTTP Request so they stay framework-agnostic and testable.
 */
final readonly class ClientData
{
    public function __construct(
        public string $name,
        public ?string $industry,
        public ?string $website,
        public ?string $contactEmail,
        public ?string $contactPhone,
        public ?string $country,
        public ?string $notes,
        public ?int $ownerId,
    ) {}

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function fromValidated(array $validated): self
    {
        return new self(
            name: (string) $validated['name'],
            industry: self::nullableString($validated, 'industry'),
            website: self::nullableString($validated, 'website'),
            contactEmail: self::nullableString($validated, 'contact_email'),
            contactPhone: self::nullableString($validated, 'contact_phone'),
            country: self::nullableString($validated, 'country'),
            notes: self::nullableString($validated, 'notes'),
            ownerId: isset($validated['owner_id']) ? (int) $validated['owner_id'] : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'industry' => $this->industry,
            'website' => $this->website,
            'contact_email' => $this->contactEmail,
            'contact_phone' => $this->contactPhone,
            'country' => $this->country,
            'notes' => $this->notes,
            'owner_id' => $this->ownerId,
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
