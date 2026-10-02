<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Scopes\ClientTenantScope;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lightweight scoped model used only by this test file to exercise the
 * scope against a real table — avoids coupling the test to Contact/Incident
 * models before they exist.
 */
#[ScopedBy(ClientTenantScope::class)]
class ScopedTestRecord extends Model
{
    protected $table = 'scoped_test_records';

    protected $guarded = [];

    public $timestamps = false;
}

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);

    Schema::create('scoped_test_records', function (Blueprint $table): void {
        $table->id();
        $table->unsignedBigInteger('client_id');
        $table->string('label');
    });

    ScopedTestRecord::query()->insert([
        ['client_id' => 1, 'label' => 'tenant-1-a'],
        ['client_id' => 1, 'label' => 'tenant-1-b'],
        ['client_id' => 2, 'label' => 'tenant-2-a'],
        ['client_id' => 3, 'label' => 'tenant-3-a'],
    ]);
});

afterEach(function (): void {
    Schema::dropIfExists('scoped_test_records');
});

it('restricts a client user to rows matching their client_id', function (): void {
    $client = User::factory()->create(['client_id' => 1, 'password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client);

    expect(ScopedTestRecord::query()->pluck('label')->all())
        ->toEqual(['tenant-1-a', 'tenant-1-b']);
});

it('lets super admins see every row across tenants', function (): void {
    $admin = User::factory()->create(['password_changed_at' => now()]);
    $admin->assignRole(UserRole::SuperAdmin->value);

    $this->actingAs($admin);

    expect(ScopedTestRecord::query()->count())->toBe(4);
});

it('does not scope queries when no user is authenticated', function (): void {
    expect(ScopedTestRecord::query()->count())->toBe(4);
});

it('returns zero rows for a client user with no matching client_id', function (): void {
    $client = User::factory()->create(['client_id' => 999, 'password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client);

    expect(ScopedTestRecord::query()->count())->toBe(0);
});
