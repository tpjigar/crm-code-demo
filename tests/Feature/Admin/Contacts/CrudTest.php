<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contact;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Inertia\Testing\AssertableInertia;
use Spatie\Activitylog\Models\Activity;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function contactsAdmin(): User
{
    $u = User::factory()->create(['password_changed_at' => now()]);
    $u->assignRole(UserRole::SuperAdmin->value);

    return $u;
}

it('lists contacts for a super admin', function (): void {
    Contact::factory()->count(3)->create();

    $this->actingAs(contactsAdmin())
        ->get('/admin/contacts')
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/contacts/index')
            ->has('contacts.data', 3),
        );
});

it('shows raw email + phone to super admins (not masked)', function (): void {
    $contact = Contact::factory()->create([
        'email' => 'raw@example.test',
        'phone' => '+15558675309',
    ]);

    $this->actingAs(contactsAdmin())
        ->get("/admin/contacts/{$contact->id}")
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('admin/contacts/show')
            ->where('contact.data.email', 'raw@example.test')
            ->where('contact.data.phone', '+15558675309')
            ->where('contact.data.email_masked', false)
            ->where('contact.data.phone_masked', false),
        );
});

it('creates a contact and demotes any existing primary when the new one is primary', function (): void {
    $client = Client::factory()->create();
    $existing = Contact::factory()->forClient($client)->primary()->create();

    $this->actingAs(contactsAdmin())
        ->post('/admin/contacts', [
            'client_id' => $client->id,
            'first_name' => 'Lane',
            'last_name' => 'Mercer',
            'email' => 'lane@acme.test',
            'phone' => '+15550100',
            'is_primary' => true,
        ])
        ->assertRedirect();

    expect($existing->refresh()->is_primary)->toBeFalse()
        ->and(Contact::where('client_id', $client->id)->where('is_primary', true)->count())->toBe(1);
});

it('rejects invalid payloads with 422 session errors', function (): void {
    $this->actingAs(contactsAdmin())
        ->from('/admin/contacts/create')
        ->post('/admin/contacts', [
            'client_id' => 999999,
            'first_name' => '',
            'last_name' => '',
            'email' => 'not-an-email',
        ])
        ->assertSessionHasErrors(['client_id', 'first_name', 'last_name', 'email']);
});

it('updates a contact via the update endpoint', function (): void {
    $contact = Contact::factory()->create(['first_name' => 'Old']);

    $this->actingAs(contactsAdmin())
        ->put("/admin/contacts/{$contact->id}", [
            'client_id' => $contact->client_id,
            'first_name' => 'New',
            'last_name' => $contact->last_name,
            'job_title' => $contact->job_title,
            'email' => $contact->email,
            'phone' => $contact->phone,
            'is_primary' => false,
        ])
        ->assertRedirect(route('admin.contacts.show', $contact));

    expect($contact->fresh()->first_name)->toBe('New');
});

it('soft-deletes a contact via the destroy endpoint', function (): void {
    $contact = Contact::factory()->create();

    $this->actingAs(contactsAdmin())
        ->delete("/admin/contacts/{$contact->id}")
        ->assertRedirect(route('admin.contacts.index'));

    expect(Contact::find($contact->id))->toBeNull()
        ->and(Contact::withTrashed()->find($contact->id))->not->toBeNull();
});

it('records an activitylog entry when a contact is created', function (): void {
    $client = Client::factory()->create();

    $this->actingAs(contactsAdmin())
        ->post('/admin/contacts', [
            'client_id' => $client->id,
            'first_name' => 'Logged',
            'last_name' => 'Person',
        ]);

    $logs = Activity::query()->where('log_name', 'contact')->get();

    expect($logs)->not->toBeEmpty()
        ->and($logs->first()->description)->toBe('created');
});
