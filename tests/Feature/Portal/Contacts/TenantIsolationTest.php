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

it('only lists contacts of the viewer\'s own tenant', function (): void {
    $own = Client::factory()->create();
    $other = Client::factory()->create();

    Contact::factory()->forClient($own)->count(2)->create();
    Contact::factory()->forClient($other)->count(3)->create();

    $viewer = User::factory()->create(['client_id' => $own->id, 'password_changed_at' => now()]);
    $viewer->assignRole(UserRole::Client->value);

    $this->actingAs($viewer)
        ->get('/portal/contacts')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('contacts.data', 2),
        );
});

it('blocks direct access to another tenant\'s contact via a guessed route id', function (): void {
    $own = Client::factory()->create();
    $other = Client::factory()->create();
    $leakTarget = Contact::factory()->forClient($other)->create();

    $viewer = User::factory()->create(['client_id' => $own->id, 'password_changed_at' => now()]);
    $viewer->assignRole(UserRole::Client->value);

    $response = $this->actingAs($viewer)->get("/portal/contacts/{$leakTarget->id}");

    // The ClientTenantScope hides the row, so route-model binding returns 404.
    // If a future refactor removes the scope, the ContactPolicy's same-tenant
    // check must still produce a 403 — either outcome blocks the leak.
    expect([404, 403])->toContain($response->status());
});

it('denies the admin contacts module to client-role users entirely', function (): void {
    $contact = Contact::factory()->create();

    $client = User::factory()->create(['password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get('/admin/contacts')
        ->assertForbidden();

    // 404 is actually preferable here (does not disclose row existence),
    // and the ClientTenantScope hides the row before route-model binding;
    // either outcome blocks access.
    $show = $this->actingAs($client)->get("/admin/contacts/{$contact->id}");
    expect([403, 404])->toContain($show->status());

    $delete = $this->actingAs($client)->delete("/admin/contacts/{$contact->id}");
    expect([403, 404])->toContain($delete->status());
});
