# CRM Code Demo — Security-First Multi-Tenant CRM

A production-grade mini-CRM built as a code sample for a cybersecurity-sector client, demonstrating how I write **secure, SOLID, well-tested Laravel + React code**.

The repo is deliberately small in scope and deliberately thorough in craftsmanship: fewer modules, more depth per module. Every feature exists because it exercises a technique a reviewer would want to see.

---

## At a glance

| | |
|---|---|
| **Stack** | Laravel 13 (PHP 8.4) · Inertia.js v2 · React 19 · TypeScript 5 (strict) · Tailwind v4 · shadcn/ui · MySQL 8 · Pest 3 |
| **Panels** | Super Admin (`/admin/*`) · Client Portal (`/portal/*`) — role-based, isolated |
| **Modules** | Clients · Contacts (with PII masking) · Incidents (with workflow state machine) · Security Center (sessions + audit log) |
| **Tests** | **136 passing · 487 assertions · Pest 3** |
| **Static analysis** | **PHPStan level 6 — 0 errors** (Larastan) |
| **Formatter** | **Laravel Pint — clean**, strict-types enforced |
| **Frontend** | **TypeScript strict — clean**, **Vite build ~2.5s** |
| **Branches** | Git Flow with `production` tier · 23 PRs merged · every commit as `tp.jigar@gmail.com` |

---

## Why this repo is worth reviewing

- **Security is designed in, not sprinkled on.** 2FA, lockout, strong password policy, 90-day expiry, HSTS/CSP/X-Frame headers, suspicious-login detection, mandatory tenant isolation via a global Eloquent scope, PII masking on the server, forged-payload defense on the portal, audit log of every mutation, and a session revocation UI — all backed by tests.
- **SOLID is visible everywhere.** Interfaces + DI (`ContactServiceInterface`), single-responsibility actions (`CreateContactAction`, `UpdateIncidentAction`), DTOs between HTTP and domain, policies, observers, enums with behavior, and a dedicated `IncidentWorkflowService` state machine.
- **Hand-written, no admin-panel generator.** Controllers, forms, services, policies, and React pages are custom — a reviewer can see how I actually architect code, not what Filament/Nova generated.
- **Git Flow discipline.** Every feature follows `feature → develop → main → production` with three PRs and no branch deletions. The release history itself is the audit trail.

See **[docs/CASE_STUDY.md](docs/CASE_STUDY.md)** for the full engineering walkthrough.

---

## Quick start

```bash
git clone https://github.com/tpjigar/crm-code-demo.git
cd crm-code-demo

cp .env.example .env
# edit DB_* in .env — defaults assume MySQL on localhost

composer install
npm install

php artisan key:generate
php artisan migrate --seed    # seeds admin + 8 demo clients, 34 contacts, 20 incidents
npm run build

php artisan serve
```

### Demo logins

| Role | Email | Password |
|---|---|---|
| Super Admin | `admin@crm-demo.test` | `ChangeMe123!` |
| Client Portal (tenant 1) | `client1@crm-demo.test` | `DemoPass123!` |
| Client Portal (tenant 2) | `client2@crm-demo.test` | `DemoPass123!` |
| … up to `client8@crm-demo.test` | | `DemoPass123!` |

Each portal user sees only **their own tenant's** contacts and incidents — try signing in as `client1` and then `client2` to see the isolation live.

---

## Feature matrix

### Authentication & authorization

| Feature | Where to look |
|---|---|
| Email + password login via **Laravel Fortify** | `config/fortify.php` |
| **TOTP two-factor authentication** (QR + recovery codes) | starter kit + `settings/two-factor-auth.tsx` |
| **Login lockout** after 5 failed attempts | Fortify `limiters` + `RateLimiter` in `AppServiceProvider` |
| **Strong password policy** — 12+ chars, mixed case, number, symbol | [`app/Rules/StrongPasswordRule.php`](app/Rules/StrongPasswordRule.php) |
| **Password expiry** (90 days) with soft-redirect to change page | [`app/Http/Middleware/EnsurePasswordNotExpired.php`](app/Http/Middleware/EnsurePasswordNotExpired.php) |
| **Role-based access control** (super_admin, client) via Spatie Permission | [`app/Enums/UserRole.php`](app/Enums/UserRole.php), `database/seeders/RolePermissionSeeder.php` |
| **Role-based login redirect** (admin → `/admin/dashboard`, client → `/portal/dashboard`) | [`app/Http/Responses/LoginResponse.php`](app/Http/Responses/LoginResponse.php) |
| **Suspicious-login detection** (IP change → email notification) | [`app/Listeners/Auth/RecordLoginMetadata.php`](app/Listeners/Auth/RecordLoginMetadata.php) |
| **CSP, HSTS, X-Frame-Options, Referrer-Policy** headers | [`app/Http/Middleware/SecurityHeaders.php`](app/Http/Middleware/SecurityHeaders.php) |

### Multi-tenant isolation

| Feature | Where to look |
|---|---|
| **ClientTenantScope** — global Eloquent scope that auto-filters queries by the authenticated user's `client_id` | [`app/Models/Scopes/ClientTenantScope.php`](app/Models/Scopes/ClientTenantScope.php) |
| Applied to `Contact` and `Incident` via `#[ScopedBy]` | [`app/Models/Contact.php`](app/Models/Contact.php), [`app/Models/Incident.php`](app/Models/Incident.php) |
| **Defense in depth**: policies also re-check `client_id` on direct-ID requests to defeat route-ID guessing | [`app/Policies/ContactPolicy.php`](app/Policies/ContactPolicy.php) |
| **Forged-payload defense**: portal controllers never trust `client_id` from the request body; they inject it from the authenticated user | [`app/Http/Controllers/Portal/IncidentController.php:65`](app/Http/Controllers/Portal/IncidentController.php) |

### Business modules

| Module | Features |
|---|---|
| **Clients** | Full REST CRUD, soft delete, owner assignment, **Security Score** derived from signal completeness, ClientRiskLevel enum with tiering |
| **Contacts** | Full REST CRUD, primary-contact invariant enforced in DB transaction, **server-side PII masking** (`j******e@a********.com`, `*******5309`) for client-role viewers, portal read-only view |
| **Incidents** | Full REST CRUD, **status state machine** (`new → investigating → resolved → closed`, re-open allowed), **SLA tracking** (critical=1h, high=4h, medium=24h, low=72h), assignee workflow, portal self-service reporting with reference `INC-XXXXXXXX` |
| **Security Center** | **Session manager** with revoke (self-session excluded), **audit log viewer** with 5 filters and JSON diff drawer |

### SOLID & architecture

| Principle | Example |
|---|---|
| **S**ingle Responsibility | `CreateContactAction`, `UpdateIncidentAction`, `IncidentWorkflowService` — one job each |
| **O**pen/Closed | `ClientRiskLevel::fromScore()` and `IncidentStatus::allowedNext()` extend by adding enum cases, not changing callers |
| **L**iskov | Services swap by binding — a test implementation would work in place of production |
| **I**nterface segregation | Service interfaces expose only what callers use (`ContactServiceInterface` is 4 methods, not a god-service) |
| **D**ependency Inversion | Controllers type-hint interfaces; `DomainServiceProvider` binds concrete implementations |

Other patterns: **DTOs** between HTTP and domain, **FormRequests** for authorization + validation, **Policies** for row-level checks, **Observers** for lifecycle hooks, **Enums with behavior** (`label()`, `badgeColor()`, `canTransitionTo()`).

### Observability

| | |
|---|---|
| Every mutation on `Client`, `Contact`, `Incident` is written to `activity_log` with the acting user, old → new values, and timestamp (**Spatie ActivityLog**) |
| Admin can filter the audit log by log name, event, causer, date range, and full-text search |
| Expand any row to see the full properties payload as JSON |

### Testing

| Tier | Count | Examples |
|---|---|---|
| Feature | 110+ | HTTP controllers, FormRequest validation, policy denials, Inertia payload shape |
| Integration | 20+ | Workflow transitions, tenant isolation, masking, SLA breach detection |
| Unit | 6+ | `SensitiveDataMasker` edge cases, `UserAgentParser` |

Run the suite:

```bash
php artisan test                     # full Pest suite
./vendor/bin/phpstan analyse         # level 6, 0 errors
./vendor/bin/pint --test             # style check
npx tsc --noEmit                     # TS strict
```

---

## Project structure

```
app/
├── Actions/              # Single-responsibility command classes
│   ├── Clients/
│   ├── Contacts/
│   └── Incidents/
├── Contracts/Services/   # Interfaces for DI
├── DTOs/                 # Immutable transport objects
├── Enums/                # Backed enums with behavior
├── Exceptions/
├── Http/
│   ├── Controllers/Admin/
│   ├── Controllers/Portal/
│   ├── Middleware/
│   ├── Requests/
│   ├── Resources/
│   └── Responses/
├── Listeners/Auth/
├── Models/
│   └── Scopes/           # ClientTenantScope
├── Notifications/Auth/
├── Observers/
├── Policies/
├── Providers/
├── Rules/
└── Services/
    ├── Clients/
    ├── Incidents/
    └── Security/

resources/js/
├── components/           # shadcn/ui + custom (MaskedField, IncidentBadge)
├── layouts/              # admin-layout, portal-layout, auth-layout
├── pages/
│   ├── admin/{clients, contacts, incidents, security}/
│   └── portal/{contacts, incidents}/
└── types/models.ts       # Strict-typed domain models

tests/Feature/
├── Admin/{Clients, Contacts, Incidents, Security}/
├── Auth/
├── Portal/{Contacts, Incidents}/
├── Rules/
├── Security/
└── Services/
```

---

## Git Flow

Every feature branches from `develop`, merges to `develop`, is promoted to `main` via a staging PR, and promoted to `production` via a release PR. **Branches are never deleted.** The release history on `production` is the deploy ledger.

```
feature/* ──► develop ──► main ──► production
             (PR)         (PR)      (PR)
```

Browse the trail of 23 merged PRs on the [Pull Requests tab](https://github.com/tpjigar/crm-code-demo/pulls?q=is%3Apr+is%3Amerged).

---

## Further reading

- **[docs/ARCHITECTURE.md](docs/ARCHITECTURE.md)** — layer-by-layer design decisions
- **[docs/CASE_STUDY.md](docs/CASE_STUDY.md)** — the engineering story for recruiters and clients
- **[docs/PLAN.md](docs/PLAN.md)** — original project plan
- **[docs/BRANCH_COMMIT_PLAN.md](docs/BRANCH_COMMIT_PLAN.md)** — branching strategy

---

## Author

**Jigar Patel** · [tp.jigar@gmail.com](mailto:tp.jigar@gmail.com) · [github.com/tpjigar](https://github.com/tpjigar)

Available for Laravel / React engagements. Pulling together a prospective-client walkthrough? The [case study](docs/CASE_STUDY.md) is written for that.
