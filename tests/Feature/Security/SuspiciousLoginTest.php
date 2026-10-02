<?php

declare(strict_types=1);

use App\Listeners\Auth\RecordLoginMetadata;
use App\Models\User;
use App\Notifications\Auth\SuspiciousLoginNotification;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

it('does not notify on the first ever login (no baseline IP yet)', function (): void {
    Notification::fake();
    Event::listen(Login::class, RecordLoginMetadata::class);

    $user = User::factory()->create([
        'last_login_ip' => null,
        'password_changed_at' => now(),
    ]);

    event(new Login('web', $user, false));

    Notification::assertNothingSent();

    expect($user->fresh()->last_login_ip)->not->toBeNull();
});

it('notifies the user when the login IP differs from the stored one', function (): void {
    Notification::fake();
    Event::listen(Login::class, RecordLoginMetadata::class);

    $user = User::factory()->create([
        'last_login_ip' => '203.0.113.10',
        'password_changed_at' => now(),
    ]);

    event(new Login('web', $user, false));

    Notification::assertSentTo($user, SuspiciousLoginNotification::class);
});

it('does not notify when the login IP matches the stored one', function (): void {
    Notification::fake();
    Event::listen(Login::class, RecordLoginMetadata::class);

    $requestIp = request()->ip();
    $user = User::factory()->create([
        'last_login_ip' => $requestIp,
        'password_changed_at' => now(),
    ]);

    event(new Login('web', $user, false));

    Notification::assertNothingSent();
});

it('updates last_login_ip and last_login_at on each login', function (): void {
    Event::listen(Login::class, RecordLoginMetadata::class);

    $user = User::factory()->create([
        'last_login_ip' => '203.0.113.10',
        'last_login_at' => null,
        'password_changed_at' => now(),
    ]);

    event(new Login('web', $user, false));

    $fresh = $user->fresh();
    expect($fresh->last_login_ip)->not->toBe('203.0.113.10')
        ->and($fresh->last_login_at)->not->toBeNull();
});
