# CyberSec CRM — Project Architecture

> Detailed folder/file structure following **SOLID principles** and Laravel best practices.
> Every layer has a clear responsibility — nothing is placed "wherever it fits."

---

## SOLID Principles — How We Apply Them

| Principle | How We Apply It |
|---|---|
| **S** — Single Responsibility | Action classes do ONE thing. Services orchestrate. Controllers delegate. |
| **O** — Open/Closed | Enums with methods for state transitions. New roles via config, not code edits. |
| **L** — Liskov Substitution | Service interfaces allow swapping implementations (e.g., mock in tests). |
| **I** — Interface Segregation | Separate contracts per capability — no god-interfaces. |
| **D** — Dependency Inversion | Controllers → ServiceInterface (not concrete Service). Bound in ServiceProvider. |

---

## Complete Folder Structure

```
crm-code-demo/
│
├── app/
│   │
│   ├── Actions/                              # Single-Responsibility operations
│   │   ├── Auth/
│   │   │   ├── AttemptLoginAction.php        # Only handles login attempt logic
│   │   │   ├── LogoutUserAction.php          # Only handles logout
│   │   │   ├── EnableTwoFactorAction.php     # Only enables 2FA for a user
│   │   │   ├── Verify2faCodeAction.php       # Only verifies TOTP code
│   │   │   └── RotatePasswordAction.php      # Only handles password change
│   │   ├── Clients/
│   │   │   ├── CreateClientAction.php
│   │   │   ├── UpdateClientAction.php
│   │   │   ├── DeleteClientAction.php
│   │   │   └── CalculateSecurityScoreAction.php
│   │   ├── Contacts/
│   │   │   ├── CreateContactAction.php
│   │   │   ├── UpdateContactAction.php
│   │   │   └── DeleteContactAction.php
│   │   ├── Incidents/
│   │   │   ├── CreateIncidentAction.php
│   │   │   ├── AssignIncidentAction.php
│   │   │   ├── TransitionIncidentStatusAction.php
│   │   │   └── CloseIncidentAction.php
│   │   └── Security/
│   │       ├── RevokeSessionAction.php
│   │       ├── ForceLogoutUserAction.php
│   │       └── RecordAuditLogAction.php
│   │
│   ├── Contracts/                            # Interfaces (D in SOLID)
│   │   ├── Services/
│   │   │   ├── ClientServiceInterface.php
│   │   │   ├── ContactServiceInterface.php
│   │   │   ├── IncidentServiceInterface.php
│   │   │   ├── AuditLoggerInterface.php
│   │   │   ├── TwoFactorAuthenticatorInterface.php
│   │   │   └── SessionManagerInterface.php
│   │   └── Support/
│   │       ├── DataMaskerInterface.php
│   │       └── IpGeolocatorInterface.php
│   │
│   ├── DTOs/                                 # Data Transfer Objects
│   │   ├── ClientData.php                    # Readonly class — type-safe data passing
│   │   ├── ContactData.php
│   │   ├── IncidentData.php
│   │   └── LoginAttemptData.php
│   │
│   ├── Enums/                                # PHP 8.1 backed enums
│   │   ├── IncidentStatus.php                # open, in_progress, resolved, closed
│   │   ├── IncidentPriority.php              # low, medium, high, critical
│   │   ├── UserRole.php                      # admin, manager, analyst, viewer
│   │   ├── AuditEvent.php                    # created, updated, deleted, login, etc.
│   │   └── ClientRiskLevel.php               # safe, low_risk, at_risk, critical
│   │
│   ├── Events/                               # Domain events (decoupling)
│   │   ├── Auth/
│   │   │   ├── UserLoggedIn.php
│   │   │   ├── UserLoggedOut.php
│   │   │   ├── LoginFailed.php
│   │   │   ├── AccountLocked.php
│   │   │   └── SuspiciousLoginDetected.php
│   │   └── Incidents/
│   │       ├── IncidentCreated.php
│   │       ├── IncidentAssigned.php
│   │       └── IncidentStatusChanged.php
│   │
│   ├── Exceptions/                           # Custom exception classes
│   │   ├── AccountLockedException.php
│   │   ├── InvalidTwoFactorCodeException.php
│   │   ├── PasswordExpiredException.php
│   │   └── UnauthorizedAccessException.php
│   │
│   ├── Http/
│   │   ├── Controllers/                      # Thin controllers — delegate to Services
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   ├── TwoFactorController.php
│   │   │   │   ├── PasswordResetController.php
│   │   │   │   └── EmailVerificationController.php
│   │   │   ├── Admin/                        # SUPER ADMIN PANEL controllers
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── ClientController.php
│   │   │   │   ├── ContactController.php
│   │   │   │   ├── IncidentController.php
│   │   │   │   ├── UserController.php
│   │   │   │   └── Security/
│   │   │   │       ├── SessionController.php
│   │   │   │       └── AuditLogController.php
│   │   │   └── Portal/                       # CLIENT PORTAL controllers
│   │   │       ├── DashboardController.php
│   │   │       ├── ContactController.php
│   │   │       ├── IncidentController.php
│   │   │       └── ProfileController.php
│   │   │
│   │   ├── Middleware/
│   │   │   ├── SecurityHeaders.php           # CSP, HSTS, X-Frame-Options
│   │   │   ├── EnsurePasswordNotExpired.php
│   │   │   ├── EnsureTwoFactorEnabled.php
│   │   │   ├── DetectSuspiciousLogin.php
│   │   │   ├── EnforceSessionTimeout.php
│   │   │   ├── EnsureSuperAdmin.php          # Guards /admin/* routes
│   │   │   └── EnsureClientUser.php          # Guards /portal/* routes
│   │   │
│   │   ├── Requests/                         # FormRequest for every form
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   ├── RegisterRequest.php
│   │   │   │   ├── TwoFactorRequest.php
│   │   │   │   ├── ForgotPasswordRequest.php
│   │   │   │   └── ResetPasswordRequest.php
│   │   │   ├── Clients/
│   │   │   │   ├── StoreClientRequest.php
│   │   │   │   └── UpdateClientRequest.php
│   │   │   ├── Contacts/
│   │   │   │   ├── StoreContactRequest.php
│   │   │   │   └── UpdateContactRequest.php
│   │   │   └── Incidents/
│   │   │       ├── StoreIncidentRequest.php
│   │   │       ├── UpdateIncidentRequest.php
│   │   │       └── AssignIncidentRequest.php
│   │   │
│   │   └── Resources/                        # API response transformers
│   │       ├── ClientResource.php
│   │       ├── ContactResource.php           # Includes auto-masking logic
│   │       └── IncidentResource.php
│   │
│   # Note: No Livewire — frontend lives in resources/js/ (React/TypeScript)
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── Client.php
│   │   ├── Contact.php
│   │   ├── Incident.php
│   │   ├── AuditLog.php
│   │   ├── LoginAttempt.php                  # Tracks failed logins
│   │   ├── UserSession.php                   # Active sessions tracker
│   │   └── Concerns/                         # Reusable model traits
│   │       ├── HasAuditLog.php
│   │       └── HasSoftDeletes.php
│   │
│   ├── Notifications/                        # Email / database notifications
│   │   ├── Auth/
│   │   │   ├── AccountLockedNotification.php
│   │   │   ├── SuspiciousLoginNotification.php
│   │   │   └── PasswordChangedNotification.php
│   │   └── Incidents/
│   │       └── IncidentAssignedNotification.php
│   │
│   ├── Observers/                            # Model lifecycle hooks
│   │   ├── ClientObserver.php                # Auto-audit on CRUD
│   │   ├── IncidentObserver.php
│   │   └── UserObserver.php                  # Session cleanup, etc.
│   │
│   ├── Policies/                             # Authorization (per model)
│   │   ├── ClientPolicy.php
│   │   ├── ContactPolicy.php
│   │   ├── IncidentPolicy.php
│   │   └── AuditLogPolicy.php
│   │
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php           # Policy registration
│   │   ├── EventServiceProvider.php          # Event-Listener bindings
│   │   ├── FortifyServiceProvider.php
│   │   └── DomainServiceProvider.php         # Interface → Concrete bindings
│   │
│   ├── Rules/                                # Custom validation rules
│   │   ├── StrongPasswordRule.php            # Complexity enforcement
│   │   ├── NotPwnedPasswordRule.php          # Haveibeenpwned check
│   │   ├── ValidTotpCodeRule.php
│   │   └── NoDisposableEmailRule.php
│   │
│   ├── Services/                             # Business logic layer
│   │   ├── Auth/
│   │   │   ├── LoginSecurityService.php      # Lockout, rate limit
│   │   │   ├── TwoFactorService.php          # TOTP generation + verify
│   │   │   ├── PasswordPolicyService.php     # Policy enforcement
│   │   │   └── EmailVerificationService.php
│   │   ├── ClientService.php
│   │   ├── ContactService.php
│   │   ├── IncidentService.php
│   │   ├── SecurityScoreService.php          # Domain-specific scoring
│   │   └── Security/
│   │       ├── SessionManagerService.php
│   │       ├── AuditLoggerService.php
│   │       ├── SensitiveDataMaskingService.php
│   │       └── SuspiciousLoginDetectorService.php
│   │
│   ├── Support/                              # Framework-agnostic helpers
│   │   ├── DataMasker.php                    # Phone/email masking
│   │   ├── IpGeolocator.php                  # IP → country/city
│   │   └── SecurityHeaders.php               # Header generator
│   │
│   └── Http/
│       └── Middleware/
│           └── HandleInertiaRequests.php     # Shared Inertia props (auth user, flash, permissions)
│
├── bootstrap/
├── config/
│   ├── auth.php
│   ├── cors.php
│   ├── fortify.php
│   ├── permission.php
│   ├── activitylog.php
│   └── security.php                          # Our custom security config
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── ClientFactory.php
│   │   ├── ContactFactory.php
│   │   └── IncidentFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── xxxx_create_permission_tables.php
│   │   ├── xxxx_create_activity_log_table.php
│   │   ├── xxxx_create_two_factor_columns.php
│   │   ├── xxxx_add_password_fields_to_users.php
│   │   ├── xxxx_create_login_attempts_table.php
│   │   ├── xxxx_create_user_sessions_table.php
│   │   ├── xxxx_create_clients_table.php
│   │   ├── xxxx_create_contacts_table.php
│   │   └── xxxx_create_incidents_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php          # Default roles + permissions
│       ├── AdminUserSeeder.php               # First admin
│       └── DemoDataSeeder.php                # Sample data for demo
│
├── public/
│   ├── index.php
│   └── favicon.ico
│
├── resources/
│   ├── css/
│   │   └── app.css                           # Tailwind v4 imports + shadcn tokens
│   ├── js/
│   │   ├── app.tsx                           # Inertia + React entry point
│   │   ├── ssr.tsx                           # Server-side render entry
│   │   ├── types/
│   │   │   ├── index.d.ts                    # Shared Inertia types (auth, flash)
│   │   │   ├── models.ts                     # Client, Contact, Incident interfaces
│   │   │   └── enums.ts                      # TS mirrors of PHP enums
│   │   ├── lib/
│   │   │   ├── utils.ts                      # cn() helper, formatters
│   │   │   ├── schemas/                      # Zod validation schemas
│   │   │   │   ├── client.schema.ts
│   │   │   │   ├── contact.schema.ts
│   │   │   │   └── incident.schema.ts
│   │   │   └── api/                          # Typed Inertia request helpers
│   │   ├── hooks/
│   │   │   ├── use-auth.ts                   # Access authed user from Inertia
│   │   │   ├── use-permissions.ts            # Role/permission checks
│   │   │   ├── use-toast.ts
│   │   │   └── use-debounce.ts
│   │   ├── components/
│   │   │   ├── ui/                           # shadcn/ui primitives (generated)
│   │   │   │   ├── button.tsx
│   │   │   │   ├── input.tsx
│   │   │   │   ├── dialog.tsx
│   │   │   │   ├── dropdown-menu.tsx
│   │   │   │   ├── select.tsx
│   │   │   │   ├── table.tsx
│   │   │   │   ├── toast.tsx
│   │   │   │   ├── badge.tsx
│   │   │   │   └── form.tsx
│   │   │   ├── shared/                       # Business components used by both panels
│   │   │   │   ├── StatusBadge.tsx
│   │   │   │   ├── PriorityBadge.tsx
│   │   │   │   ├── DataTable.tsx
│   │   │   │   ├── Pagination.tsx
│   │   │   │   ├── ConfirmDialog.tsx
│   │   │   │   ├── TwoFactorSetup.tsx
│   │   │   │   ├── ActiveSessionsList.tsx
│   │   │   │   └── MaskedField.tsx           # Sensitive data masking
│   │   │   ├── admin/                        # Admin-only components
│   │   │   │   └── SecurityScoreCard.tsx
│   │   │   └── portal/                       # Client Portal-only components
│   │   │       └── MyIncidentCard.tsx
│   │   ├── layouts/
│   │   │   ├── GuestLayout.tsx               # Public auth pages layout
│   │   │   ├── AdminLayout.tsx               # Super admin layout + sidebar
│   │   │   └── PortalLayout.tsx              # Client portal layout + sidebar
│   │   └── pages/                            # Inertia pages (routed by Laravel)
│   │       ├── Auth/
│   │       │   ├── Login.tsx
│   │       │   ├── Register.tsx
│   │       │   ├── VerifyEmail.tsx
│   │       │   ├── TwoFactor.tsx
│   │       │   ├── ForgotPassword.tsx
│   │       │   └── ResetPassword.tsx
│   │       ├── Admin/
│   │       │   ├── Dashboard.tsx
│   │       │   ├── Clients/
│   │       │   │   ├── Index.tsx
│   │       │   │   ├── Create.tsx
│   │       │   │   ├── Edit.tsx
│   │       │   │   └── Show.tsx
│   │       │   ├── Contacts/
│   │       │   ├── Incidents/
│   │       │   ├── Users/
│   │       │   └── Security/
│   │       │       ├── AuditLog.tsx
│   │       │       └── ActiveSessions.tsx
│   │       └── Portal/
│   │           ├── Dashboard.tsx
│   │           ├── Contacts/
│   │           ├── Incidents/
│   │           └── Profile/
│   │               └── Edit.tsx
│   └── views/
│       ├── app.blade.php                     # Inertia root HTML template (only Blade file)
│       └── emails/
│           ├── suspicious-login.blade.php
│           └── incident-assigned.blade.php
│
├── routes/
│   ├── web.php                               # Includes all route files
│   ├── auth.php                              # Login, register, 2FA (public)
│   ├── admin.php                             # /admin/* — Super Admin panel
│   ├── portal.php                            # /portal/* — Client Portal
│   └── console.php
│
├── storage/
├── tests/
│   ├── Feature/
│   │   ├── Auth/
│   │   │   ├── LoginTest.php
│   │   │   ├── RegistrationTest.php
│   │   │   ├── TwoFactorTest.php
│   │   │   ├── LockoutTest.php
│   │   │   └── PasswordPolicyTest.php
│   │   ├── Clients/
│   │   │   ├── CreateClientTest.php
│   │   │   ├── UpdateClientTest.php
│   │   │   └── ClientSecurityScoreTest.php
│   │   ├── Contacts/
│   │   ├── Incidents/
│   │   └── Security/
│   │       ├── SessionManagerTest.php
│   │       ├── AuditLogTest.php
│   │       └── SecurityHeadersTest.php
│   └── Unit/
│       ├── Services/
│       │   ├── SecurityScoreServiceTest.php
│       │   ├── TwoFactorServiceTest.php
│       │   └── SensitiveDataMaskingServiceTest.php
│       ├── Actions/
│       └── Rules/
│
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── composer.lock
├── package.json
├── phpunit.xml
├── postcss.config.js
├── tailwind.config.js
├── vite.config.js
├── PLAN.md
├── ARCHITECTURE.md                           # This file
└── README.md
```

---

## Two-Panel Architecture

The app has **two completely separate panels**, each with its own URL prefix,
layout, Livewire namespace, and middleware guard. Shared services underneath.

### Panel 1 — Super Admin (`/admin/*`)
- **Who:** Internal staff with `super_admin` role
- **Can:** Manage all clients, contacts, incidents, users, system settings
- **Sees:** Everything — no data scoping
- **Routes file:** `routes/admin.php`
- **Middleware stack:** `web, auth, verified, 2fa.required, role:super_admin`
- **Controllers:** `App\Http\Controllers\Admin\*`
- **Livewire:** `App\Livewire\Admin\*`
- **Views:** `resources/views/livewire/admin/*`
- **Layout:** `layouts/admin.blade.php` (admin sidebar, admin branding)

### Panel 2 — Client Portal (`/portal/*`)
- **Who:** External client users with `client` role
- **Can:** View own data, create own incidents, manage own contacts/profile
- **Sees:** Only records belonging to their `client_id` (scoped at query level)
- **Routes file:** `routes/portal.php`
- **Middleware stack:** `web, auth, verified, 2fa.required, role:client`
- **Controllers:** `App\Http\Controllers\Portal\*`
- **Livewire:** `App\Livewire\Portal\*`
- **Views:** `resources/views/livewire/portal/*`
- **Layout:** `layouts/portal.blade.php` (client sidebar, client branding)

### Data Scoping (critical for security)
Client-side portal uses a global **Eloquent Scope** that filters all queries
by `auth()->user()->client_id`. Even if a developer forgets a `where`, the
scope guarantees no data leaks across clients.

```php
// app/Models/Scopes/ClientTenantScope.php
public function apply(Builder $builder, Model $model): void
{
    if (auth()->check() && auth()->user()->hasRole('client')) {
        $builder->where('client_id', auth()->user()->client_id);
    }
}
```

Applied via `#[ScopedBy(ClientTenantScope::class)]` attribute on
`Contact`, `Incident`, etc.

### Login Routing
After successful login, user is redirected based on role:
- `super_admin` → `/admin/dashboard`
- `client` → `/portal/dashboard`

Handled in `LoginController` via a single switch on `$user->getRoleNames()`.

---

## Request Flow Example (SOLID in Action)

**Scenario: Super Admin creates a new Client**

```
┌──────────────────────────────────────────────────────────────────┐
│  0. React page (resources/js/pages/Admin/Clients/Create.tsx)     │
│     - React Hook Form + Zod validates client-side (UX)           │
│     - router.post('/admin/clients', formData) via Inertia        │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  1. HTTP Request → routes/admin.php                              │
│     POST /admin/clients                                          │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  2. Middleware Stack                                             │
│     - web, auth, verified, 2fa.required, password.not-expired   │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  3. ClientController@store                                       │
│     public function store(StoreClientRequest $request,          │
│                           ClientServiceInterface $service)      │
│     - Request already validated (FormRequest ran)               │
│     - Policy already checked (via authorize in FormRequest)     │
│     - Delegates to Service                                       │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  4. ClientService::create(ClientData $data)                      │
│     - Accepts a DTO (not a Request — framework-agnostic)        │
│     - Delegates to CreateClientAction                           │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  5. CreateClientAction::execute(ClientData $data)                │
│     - Creates Client model                                       │
│     - Fires ClientCreated event                                  │
│     - Returns Client                                             │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  6. ClientObserver::created()                                    │
│     - Automatically logs to AuditLog via AuditLoggerInterface   │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  7. Response (Inertia)                                           │
│     - return redirect()->route('admin.clients.show', $client)    │
│     - Session flash: ['success' => 'Client created']             │
│     - Inertia visits new page without full browser reload        │
└──────────────────────────────────────────────────────────────────┘
                              ↓
┌──────────────────────────────────────────────────────────────────┐
│  8. React (Admin/Clients/Show.tsx) renders with props            │
│     - Receives `client` prop (ClientResource shape)              │
│     - useToast() hook shows flash message                        │
│     - Zero full-page reload — Inertia swaps React component      │
└──────────────────────────────────────────────────────────────────┘
```

**What this demonstrates:**
- Controller is **5 lines**, does nothing but delegate
- FormRequest = validation + authorization in one place
- Service accepts a DTO, not an HTTP Request → testable without HTTP
- Action is the single unit of work → easily unit tested
- Observer handles cross-cutting concern (audit) → zero code in Service
- Dependency Injection via interfaces → mockable in tests
- React page is a pure prop-driven component → testable with React Testing Library
- Zod schema mirrors Laravel validation → single source of truth per feature
- No separate REST API — Inertia hands props directly to React, zero endpoint boilerplate

---

## Layer Responsibilities (Strict)

| Layer | May Do | May NOT Do |
|---|---|---|
| **Controller** | Receive request, call service, return response | Business logic, DB queries, validation |
| **FormRequest** | Validate input, authorize | Change data, call services |
| **Service** | Orchestrate actions, manage transactions | Direct DB queries, HTTP concerns |
| **Action** | Execute ONE business operation | Call other actions directly (fire events instead) |
| **Model** | Define schema, relationships, scopes, casts | Business logic, send emails |
| **Observer** | React to model lifecycle events | Validate, modify unrelated data |
| **Policy** | Return boolean authorization decision | Modify data, log |
| **DTO** | Carry typed data between layers | Have methods beyond construction |
| **Enum** | Represent fixed set of values + metadata methods | Query DB |

---

## Service Binding Example (`DomainServiceProvider`)

```php
public function register(): void
{
    $this->app->bind(ClientServiceInterface::class, ClientService::class);
    $this->app->bind(ContactServiceInterface::class, ContactService::class);
    $this->app->bind(IncidentServiceInterface::class, IncidentService::class);
    $this->app->bind(AuditLoggerInterface::class, AuditLoggerService::class);
    $this->app->bind(TwoFactorAuthenticatorInterface::class, TwoFactorService::class);
    $this->app->bind(DataMaskerInterface::class, DataMasker::class);
}
```

Controllers type-hint the **interface**, Laravel injects the concrete class.
In tests, swap for a mock — zero code change in controllers.

---

## File Count Estimate

### Backend (PHP)
| Category | Count |
|---|---|
| Actions | ~18 |
| Services + Interfaces | ~20 |
| Controllers (Admin + Portal) | ~16 |
| Form Requests | ~15 |
| Models | ~7 |
| Policies | ~4 |
| Enums | ~5 |
| DTOs | ~4 |
| Events + Listeners | ~12 |
| Observers | ~3 |
| Custom Rules | ~4 |
| Middleware (custom) | ~7 |
| Migrations | ~12 |
| Resources (Inertia) | ~5 |

### Frontend (TypeScript/React)
| Category | Count |
|---|---|
| Pages (Admin + Portal + Auth) | ~30 |
| shadcn/ui components | ~15 |
| Shared business components | ~10 |
| Layouts | ~3 |
| Zod schemas | ~6 |
| TypeScript types/interfaces | ~10 |
| Custom hooks | ~5 |

### Tests
| Category | Count |
|---|---|
| Feature tests (Pest) | ~25 |
| Unit tests (Pest) | ~15 |

**Total: ~250 files** — demonstrates full-stack depth without padding.

---

## Frontend SOLID (TypeScript/React)

The same SOLID principles apply on the React side:

| Principle | How We Apply It |
|---|---|
| **S** — Single Responsibility | Each component does ONE thing. Business logic in hooks, not components. |
| **O** — Open/Closed | shadcn/ui components extended via `className` + variants, never patched. |
| **L** — Liskov Substitution | All form components accept the same `FieldProps<T>` contract. |
| **I** — Interface Segregation | Typed props per component — no `any`, no god-props objects. |
| **D** — Dependency Inversion | Pages receive data via Inertia props, don't fetch themselves — swappable. |

### Frontend Rules
- **No `any`** in TypeScript — ever. Strict mode enabled.
- **No inline styles** — Tailwind + shadcn variants only.
- **Zod schema shared** between client validation (RHF) and type derivation.
- **Hooks** for all stateful logic — components stay presentational.
- **Server is source of truth** — client only mirrors, never diverges.

---

*Architecture doc last updated: October 2026*
