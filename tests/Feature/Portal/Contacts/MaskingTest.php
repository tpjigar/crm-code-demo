<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function portalClientFor(Client $client): User
{
    $u = User::factory()->create([
        'client_id' => $client->id,
        'password_changed_at' => now(),
    ]);
    $u->assignRole(UserRole::Client->value);

    return $u;
}

it('masks email + phone for a client-role viewer on the portal index', function (): void {
    $client = Client::factory()->create();
    Contact::factory()->forClient($client)->create([
        'email' => 'jane.doe@acme-corp.com',
        'phone' => '+15558675309',
    ]);

    $viewer = portalClientFor($client);

    $this->actingAs($viewer)
        ->get('/portal/contacts')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('portal/contacts/index')
            ->has('contacts.data', 1)
            ->where('contacts.data.0.email_masked', true)
            ->where('contacts.data.0.phone_masked', true)
            ->where('contacts.data.0.email', 'j******e@a********.com')
            ->where('contacts.data.0.phone', '*******5309'),
        );
});

it('does not include raw email or phone anywhere in the portal payload for a client viewer', function (): void {
    $client = Client::factory()->create();
    Contact::factory()->forClient($client)->create([
        'email' => 'raw-leak@secret.test',
        'phone' => '+15558675309',
    ]);

    $viewer = portalClientFor($client);

    $response = $this->actingAs($viewer)->get('/portal/contacts')->assertOk();
    $json = $response->getContent();

    expect($json)->not->toContain('raw-leak@secret.test')
        ->and($json)->not->toContain('+15558675309');
});
