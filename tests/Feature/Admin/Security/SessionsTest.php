<?php

declare(strict_types=1);

use App\Enums\UserRole;
use App\Models\Session;
use App\Models\User;
use App\Services\Security\UserAgentParser;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\DB;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

function securityAdmin(): User
{
    $u = User::factory()->create(['password_changed_at' => now()]);
    $u->assignRole(UserRole::SuperAdmin->value);

    return $u;
}

function makeSession(string $id, ?int $userId, string $userAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120.0', int $lastActivity = 0): void
{
    DB::table('sessions')->insert([
        'id' => $id,
        'user_id' => $userId,
        'ip_address' => '203.0.113.42',
        'user_agent' => $userAgent,
        'payload' => base64_encode(serialize([])),
        'last_activity' => $lastActivity ?: time(),
    ]);
}

it('lists all active sessions for a super admin', function (): void {
    $user = User::factory()->create();
    makeSession('sess-a', $user->id);
    makeSession('sess-b', $user->id);

    $this->actingAs(securityAdmin())
        ->get('/admin/security/sessions')
        ->assertOk()
        ->assertSee('sess-a')
        ->assertSee('sess-b');
});

it('denies session viewing to client-role users', function (): void {
    $client = User::factory()->create(['password_changed_at' => now()]);
    $client->assignRole(UserRole::Client->value);

    $this->actingAs($client)
        ->get('/admin/security/sessions')
        ->assertForbidden();
});

it('revokes a target session via the destroy endpoint', function (): void {
    $target = User::factory()->create();
    makeSession('revoke-me', $target->id);

    expect(Session::find('revoke-me'))->not->toBeNull();

    $this->actingAs(securityAdmin())
        ->delete('/admin/security/sessions/revoke-me')
        ->assertRedirect();

    expect(Session::find('revoke-me'))->toBeNull();
});

it('derives browser + os from the user-agent string', function (): void {
    $parser = new UserAgentParser;

    expect($parser->parse('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120.0'))
        ->toMatchArray(['browser' => 'Chrome', 'os' => 'macOS', 'device' => 'Desktop'])
        ->and($parser->parse('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0) Safari/605.1'))
        ->toMatchArray(['browser' => 'Safari', 'os' => 'iOS'])
        ->and($parser->parse('curl/8.4.0'))
        ->toMatchArray(['browser' => 'curl', 'device' => 'Script']);
});
