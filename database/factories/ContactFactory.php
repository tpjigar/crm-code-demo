<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Client;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'job_title' => fake()->jobTitle(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->e164PhoneNumber(),
            'is_primary' => false,
            'notes' => fake()->optional(0.2)->sentence(),
        ];
    }

    public function forClient(Client $client): self
    {
        return $this->state(fn (): array => ['client_id' => $client->id]);
    }

    public function primary(): self
    {
        return $this->state(fn (): array => ['is_primary' => true]);
    }
}
