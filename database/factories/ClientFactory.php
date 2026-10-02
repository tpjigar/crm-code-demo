<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'industry' => fake()->randomElement([
                'Healthcare', 'Finance', 'Technology', 'Retail',
                'Manufacturing', 'Education', 'Government',
            ]),
            'website' => fake()->url(),
            'contact_email' => fake()->companyEmail(),
            'contact_phone' => fake()->phoneNumber(),
            'country' => fake()->countryCode(),
            'notes' => fake()->optional(0.3)->paragraph(),
            'owner_id' => null,
        ];
    }

    public function ownedBy(User $owner): self
    {
        return $this->state(fn (): array => ['owner_id' => $owner->id]);
    }
}
