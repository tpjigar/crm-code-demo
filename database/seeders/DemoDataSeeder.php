<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\IncidentSeverity;
use App\Enums\IncidentStatus;
use App\Enums\UserRole;
use App\Models\Client;
use App\Models\Contact;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Populates the database with a realistic data set so the demo repo
 * speaks for itself the moment someone runs `php artisan migrate --seed`.
 *
 * Creates:
 *   - 8 clients across varied industries + risk tiers
 *   - 3–5 contacts per client (one primary each)
 *   - A spread of incidents covering every (severity x status) cell
 *     so the admin dashboard shows real color
 *   - One portal user per client (same password, printed below)
 *
 * Idempotent: uses firstOrCreate on things that would collide across
 * reruns, and only seeds extras when the counts are below target.
 */
class DemoDataSeeder extends Seeder
{
    private const PORTAL_PASSWORD = 'DemoPass123!';

    /** @var array<int, array{name: string, industry: string, country: string}> */
    private const CLIENTS = [
        ['name' => 'Northwind Health', 'industry' => 'Healthcare', 'country' => 'US'],
        ['name' => 'Meridian Capital Partners', 'industry' => 'Finance', 'country' => 'GB'],
        ['name' => 'Helios Energy', 'industry' => 'Energy', 'country' => 'DE'],
        ['name' => 'Fjord Logistics', 'industry' => 'Logistics', 'country' => 'NO'],
        ['name' => 'Kestrel Robotics', 'industry' => 'Manufacturing', 'country' => 'JP'],
        ['name' => 'Lumen Learning Co-op', 'industry' => 'Education', 'country' => 'CA'],
        ['name' => 'Civix Government Cloud', 'industry' => 'Government', 'country' => 'AU'],
        ['name' => 'Aster Retail Group', 'industry' => 'Retail', 'country' => 'FR'],
    ];

    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);
        $this->call(AdminUserSeeder::class);

        /** @var User $owner */
        $owner = User::firstWhere('email', 'admin@crm-demo.test');

        foreach (self::CLIENTS as $i => $spec) {
            $client = Client::firstOrCreate(
                ['name' => $spec['name']],
                [
                    'industry' => $spec['industry'],
                    'website' => 'https://'.Str::slug($spec['name']).'.example',
                    'contact_email' => 'ops@'.Str::slug($spec['name']).'.example',
                    'contact_phone' => '+1-555-'.str_pad((string) (100 + $i), 4, '0', STR_PAD_LEFT),
                    'country' => $spec['country'],
                    'notes' => 'Managed engagement — seeded demo data.',
                    'owner_id' => $owner->id,
                ],
            );

            $this->seedContactsFor($client);
            $this->seedIncidentsFor($client, $i);
            $this->seedPortalUserFor($client, $i);
        }
    }

    private function seedContactsFor(Client $client): void
    {
        if ($client->contacts()->count() > 0) {
            return;
        }

        Contact::factory()->forClient($client)->primary()->create();
        Contact::factory()->forClient($client)->count(random_int(2, 4))->create();
    }

    private function seedIncidentsFor(Client $client, int $index): void
    {
        if (Incident::withoutGlobalScopes()->where('client_id', $client->id)->exists()) {
            return;
        }

        $cells = [
            [IncidentSeverity::Low,      IncidentStatus::Closed],
            [IncidentSeverity::Medium,   IncidentStatus::Resolved],
            [IncidentSeverity::High,     IncidentStatus::Investigating],
            [IncidentSeverity::Critical, IncidentStatus::New],
        ];

        // Rotate through cells so admin dashboard shows every tier.
        $cell = $cells[$index % count($cells)];

        Incident::factory()
            ->forClient($client)
            ->withSeverity($cell[0])
            ->withStatus($cell[1])
            ->create([
                'reference' => 'INC-'.strtoupper(Str::random(8)),
                'title' => $this->incidentTitleFor($cell[0]),
                'description' => $this->incidentBodyFor($cell[0]),
                'reported_by_id' => null,
            ]);

        // Add 1-2 "ambient" historical closed incidents so timelines look alive.
        Incident::factory()
            ->forClient($client)
            ->withStatus(IncidentStatus::Closed)
            ->count(random_int(1, 2))
            ->create();
    }

    private function seedPortalUserFor(Client $client, int $index): void
    {
        $email = 'client'.($index + 1).'@crm-demo.test';

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => Str::of($client->name)->before(' ')->append(' Portal User'),
                'password' => Hash::make(self::PORTAL_PASSWORD),
                'email_verified_at' => now(),
                'password_changed_at' => now(),
                'client_id' => $client->id,
            ],
        );

        if (! $user->hasRole(UserRole::Client->value)) {
            $user->assignRole(UserRole::Client->value);
        }
    }

    private function incidentTitleFor(IncidentSeverity $sev): string
    {
        return match ($sev) {
            IncidentSeverity::Low => 'TLS certificate expires in 30 days',
            IncidentSeverity::Medium => 'Multiple failed admin logins from new ASN',
            IncidentSeverity::High => 'Suspected credential-stuffing wave against portal',
            IncidentSeverity::Critical => 'Outbound traffic to known C2 host detected',
        };
    }

    private function incidentBodyFor(IncidentSeverity $sev): string
    {
        return match ($sev) {
            IncidentSeverity::Low => 'Routine advisory — the production ingress cert expires in 30 days. Renewal scheduled via ACME. No service impact expected.',
            IncidentSeverity::Medium => 'Observed 12 failed logins in 4 minutes from an ASN not seen in the last 90 days. Lockout engaged. Requesting user confirmation of travel.',
            IncidentSeverity::High => 'Spike of ~800 login attempts across 42 accounts, distinct IPs, low success rate. Pattern consistent with credential-stuffing. Rate-limit tightened and CAPTCHA deployed.',
            IncidentSeverity::Critical => 'Egress to a host present on the latest CTI feed. All sessions revoked pending forensic triage. Containment underway; expect further comms within the hour.',
        };
    }
}
