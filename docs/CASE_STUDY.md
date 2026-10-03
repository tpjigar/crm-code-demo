# Case Study — CRM Code Demo

> A production-grade mini-CRM, built as a code sample for a cybersecurity-sector client. Written to show how I architect Laravel + React applications where correctness, security, and maintainability all matter.

---

## 1. The brief

The client runs a cybersecurity practice and wanted to see a self-contained code sample before deciding whether to engage me for an upcoming CRM project. The constraints:

- **Repo must look like production, not a toy.** No `php artisan tinker` demos, no `routes/web.php` with inline closures.
- **Security-first.** Every feature a cybersecurity reviewer would check for — 2FA, lockout, headers, tenant isolation, PII handling, audit trail — should be present and tested.
- **SOLID and testable.** Not just "has tests", but a design where tests are the natural artifact of good structure.
- **Full stack.** The client wanted to assess both backend and frontend craft.
- **No admin-panel generators.** Filament, Nova, or Backpack would hide the architecture. Controllers, forms, services, and React pages must be hand-written.

I built this repo over a focused engineering sprint, following strict Git Flow, with every commit attributed to `tp.jigar@gmail.com` and every change promoted through three environments (`develop → main → production`).

---

## 2. Tech stack and why

| Layer | Choice | Why |
|---|---|---|
| Backend | **Laravel 13 (PHP 8.4)** | Latest stable; readonly classes, enums, attributes land cleanly |
| Auth | **Laravel Fortify** | Decoupled from Jetstream so the UI stays mine; TOTP + lockout out of the box |
| Permissions | **Spatie Permission ^8.3** | Role + permission matrix with cache; interoperable with FormRequests and Policies |
| Audit | **Spatie ActivityLog ^5.1** | Model-level `LogsActivity` trait writes causer, event, and property diffs with no controller glue |
| Frontend bridge | **Inertia.js v2** | Server-driven routes, no API duplication; keeps the mental model single |
| UI | **React 19 + TypeScript 5 (strict) + Tailwind v4 + shadcn/ui** | Modern, accessible primitives; strict types catch shape mismatches at compile time |
| Testing | **Pest 3** | Expressive syntax, Laravel integration, parallel-friendly |
| Static analysis | **PHPStan level 6 + Larastan** | Catches generics violations and type mismatches CI-cheaply |
| Style | **Laravel Pint** with strict-types fixer | Enforces `declare(strict_types=1)` and ordered imports |

The combination gives me the strongest static guarantees PHP currently offers (strict types, generics via PHPDoc, enum behavior, readonly DTOs) without reaching for a framework the client wouldn't use.

---

## 3. The modules

Four domains, each chosen to exercise a different part of the stack.

### 3.1 Clients
Baseline CRUD module to establish the pattern. Introduces:
- **Service layer** (`ClientServiceInterface` + `ClientService` + three single-responsibility Actions)
- **FormRequests** for authorization and validation separation
- **Policies** for row-level checks
- **ClientResource** for stable API output
- **ClientObserver** wiring Spatie ActivityLog with an auto-computed **SecurityScore** (ClientRiskLevel enum maps score → tier → badge color)
- Shadcn-styled React pages: list, create, edit, show (with the Security Score card as the visual anchor)

### 3.2 Contacts — PII masking and tenant scope
First consumer of the global `ClientTenantScope`. Introduces:
- **`SensitiveDataMasker`** service: deterministic masks for emails (`j******e@a********.com`) and phones (`*******5309`)
- **Server-side masking** in `ContactResource` — raw PII never crosses the wire for a client-role viewer without the `contacts.view_sensitive` permission
- **Primary-contact invariant** enforced inside a DB transaction (demotes any existing primary when a new one is created)
- **Click-to-reveal** `MaskedField` React component that renders a "masked" badge for pre-masked values so users understand why they can't un-mask client-side (the raw value never arrived)

### 3.3 Incidents — state machine and SLA
Workflow module. Introduces:
- **`IncidentStatus` enum** with `allowedNext()` method encoding the state machine (`new → investigating → resolved → closed`, with `resolved → investigating` for re-open)
- **`IncidentWorkflowService::transition()`** is the only path that writes `status`. Illegal transitions throw `InvalidIncidentTransitionException` which the controller catches and surfaces as HTTP 422.
- **`IncidentSeverity` enum** with ISO/IEC 27035-aligned ack SLAs: critical=1h, high=4h, medium=24h, low=72h
- **`Incident::slaBreached()`** is a computed derivation from `created_at + severity SLA` — never a stored flag, so it can't drift from reality
- Portal self-service reporting with **forged-payload defense**: the controller injects `client_id` from `$user->client_id`, never trusts the request body
- UI reads `allowed_next` from the server, so the admin page can't *offer* an illegal transition

### 3.4 Security Center
SOC-facing views. Introduces:
- **`SessionManager`** service over Laravel's `sessions` table (database driver). Lists active logins with parsed UA, allows per-session revoke. The admin's own current session is excluded so they can't lock themselves out.
- **`UserAgentParser`** — a lightweight custom summarizer (enough signal to tell Chrome from curl without pulling a 10MB regex database)
- **`AuditLogService`** wraps Spatie ActivityLog as a read-only query layer with 5 filters (log name, event, causer, date range, full-text search)
- Expand-row drawer renders the full `properties` payload as JSON so an admin can inspect exactly what changed and when

---

## 4. Security posture — what a reviewer should check

| Threat | Mitigation | Where |
|---|---|---|
| **Credential stuffing** | 5-attempt lockout per IP + email; strong password rule; 90-day password expiry; TOTP 2FA | Fortify config + `StrongPasswordRule` + `EnsurePasswordNotExpired` middleware |
| **Session hijacking** | HSTS, CSP, X-Frame-Options, Referrer-Policy; HTTPS-only cookies in prod; session revocation UI | `SecurityHeaders` middleware; `SessionController` |
| **CSRF** | Laravel default `VerifyCsrfToken` middleware; all state-changing Inertia requests use the Inertia header | framework default |
| **Mass assignment** | Every model declares `#[Fillable([...])]` — I never use `$guarded = []` | all `app/Models/*.php` |
| **SQL injection** | 100% query builder / Eloquent; no raw `DB::statement` with user input | repo-wide — `grep` confirms |
| **Tenant data leakage** | Global `ClientTenantScope` on `Contact` + `Incident`; policy-level same-tenant check for direct-ID requests; grep-level test asserts no raw PII appears in portal payloads | `app/Models/Scopes/`, `tests/Feature/Portal/*/TenantIsolationTest.php` |
| **PII exposure** | `SensitiveDataMasker` applied server-side in `ContactResource` for client-role viewers | `app/Services/Security/SensitiveDataMasker.php` |
| **Forged request payloads** | Portal `IncidentController@store` injects `client_id` from the authenticated user, never trusts the body (test proves it) | `app/Http/Controllers/Portal/IncidentController.php` |
| **Suspicious logins** | Login listener detects IP change vs. last known IP; emails the account owner | `app/Listeners/Auth/RecordLoginMetadata.php` |
| **Audit gap** | Every mutation on `Client`, `Contact`, `Incident` writes an activity log row with causer, event, and property diffs — filterable in the admin UI | `app/Services/Security/AuditLogService.php` |
| **Illegal state transitions** | Incident status writes go through `IncidentWorkflowService` which checks the state machine; direct `$incident->status = ...` is never done in controllers | `app/Services/Incidents/IncidentWorkflowService.php` |

Every one of these is covered by a Pest test.

---

## 5. SOLID in practice

Not as a bullet list in a README — as the shape of the code. A few concrete examples:

**Single Responsibility** — a `Contact` write is three objects:

```php
// Controller: HTTP boundary only
public function store(StoreContactRequest $request): RedirectResponse
{
    $contact = $this->contacts->create(ContactData::fromValidated($request->validated()));
    return redirect()->route('admin.contacts.show', $contact)->with('success', 'Contact created.');
}

// Service: orchestration
public function create(ContactData $data): Contact
{
    return $this->create->execute($data);
}

// Action: the one thing — mutate state atomically
public function execute(ContactData $data): Contact
{
    return DB::transaction(function () use ($data): Contact {
        if ($data->isPrimary) {
            Contact::query()->where('client_id', $data->clientId)
                ->where('is_primary', true)->update(['is_primary' => false]);
        }
        return Contact::create($data->toArray());
    });
}
```

**Dependency Inversion** — controller depends on the interface, provider binds the concrete:

```php
// Controller
public function __construct(
    private readonly ContactServiceInterface $contacts,
) {}

// DomainServiceProvider
private function domainBindings(): array
{
    return [
        ContactServiceInterface::class => ContactService::class,
        IncidentServiceInterface::class => IncidentService::class,
        // ...
    ];
}
```

**Open/Closed** — SLA logic lives on the enum, callers never branch on severity:

```php
public function ackSlaHours(): int
{
    return match ($this) {
        self::Low      => 72,
        self::Medium   => 24,
        self::High     => 4,
        self::Critical => 1,
    };
}
```

Adding a new severity is one `case` plus one `match` arm — zero changes to the dozen callers.

---

## 6. Testing strategy

**136 Pest tests, 487 assertions, PHPStan level 6 clean.** Not aiming for a coverage percentage; aiming for every security-sensitive path and every state-machine branch to be covered.

The most valuable tests in the repo:

- **`tests/Feature/Portal/Contacts/MaskingTest.php`** — grep-level assertion that raw PII (`raw-leak@secret.test`) **never** appears in the portal's JSON payload. If a future refactor accidentally bypasses the resource, this test catches it.
- **`tests/Feature/Portal/Incidents/TenantIsolationTest.php`** — a client user posts `{client_id: <other tenant>}`; test asserts the incident still lands in their own tenant. Proves the forged-payload defense works.
- **`tests/Feature/Services/Incidents/WorkflowTest.php`** — exhaustive state-machine coverage. Happy path, re-open, every illegal transition, and the acknowledged_at preservation invariant.
- **`tests/Feature/Admin/Incidents/SlaTest.php`** — SLA breach detection with real time arithmetic.
- **`tests/Feature/Admin/Security/AuditLogTest.php`** — confirms a real `Client::update()` by a logged-in admin lands in `activity_log` with the right causer.

---

## 7. Release discipline

Git Flow with a `production` tier, three PRs per feature, zero branches deleted.

```
feature/project-setup      → develop → main → production  (PRs #1 → #2)
feature/auth-security      → develop → main → production  (PRs #3 → #4 → #5)
feature/admin-panel        → develop → main → production  (PRs #6 → #7 → #8)
feature/client-portal      → develop → main → production  (PRs #9 → #10 → #11)
feature/clients-module     → develop → main → production  (PRs #12 → #13 → #14)
feature/contacts-module    → develop → main → production  (PRs #15 → #16 → #17)
feature/incidents-module   → develop → main → production  (PRs #18 → #19 → #20)
feature/security-center    → develop → main → production  (PRs #21 → #22 → #23)
release/v1.0.0             → develop → main → production  (this release)
```

**Branches are never deleted.** The release history is the deploy ledger. On a real engagement I'd use the same shape with GitHub Actions gating each promotion on tests + static analysis.

---

## 8. What I'd do differently on a longer engagement

Being honest about scope — things I made deliberate decisions *not* to build because they'd pad the sample without adding review signal:

- **Horizon / queue workers.** The suspicious-login email dispatches a queued notification; a real deployment would run Horizon. Config is present; worker is out of scope.
- **End-to-end tests (Dusk / Playwright).** The Inertia payload assertions cover the controller contract; browser-level tests would come with a real CI pipeline.
- **Multi-factor policy per role.** The admin's 2FA is optional today; a real engagement would make it mandatory for super_admin before first dashboard load.
- **IP allowlist for the admin panel.** Trivial middleware addition; omitted so the demo runs anywhere.
- **Rate-limiting beyond login.** Only login is rate-limited; a production API surface would get bucket limits on expensive endpoints.
- **GitHub Actions CI.** The repo is set up for it (Pest, PHPStan, Pint are all one-command); adding `.github/workflows/*` is a 20-line config.

Each of these is a design decision, not an oversight.

---

## 9. If you're hiring

I take craftsmanship seriously, I ship fast, and I write code that other engineers can read without a tour. If this repo matches what you're looking for, email me at [tp.jigar@gmail.com](mailto:tp.jigar@gmail.com).

What I can bring to your project:
- 10+ years of Laravel in production (API, SaaS, multi-tenant)
- React/TypeScript delivery for user-facing admin dashboards
- Security-conscious design (NIST 800-53 adjacent, ISO 27035 for incident workflows)
- Discipline around tests, static analysis, and reviewable commits — not just "it works"
- Comfortable owning architecture decisions and documenting the "why" so the next engineer isn't guessing

Happy to walk through this repo on a call, or any part of it in writing.

---

**Jigar Patel** · [tp.jigar@gmail.com](mailto:tp.jigar@gmail.com) · [github.com/tpjigar](https://github.com/tpjigar)
