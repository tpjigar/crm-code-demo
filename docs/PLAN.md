# CyberSec CRM — Code Demo Project Plan

> **Purpose:** Showcase **full-stack** coding standards (backend + frontend) and
> cybersecurity-focused development for a client evaluation.
> **Author:** Jigar Patel
> **Started:** October 2026

---

## Stack

### Backend
| Layer | Technology |
|---|---|
| Framework | **Laravel 13** (PHP 8.4) — v13.34+ |
| Auth Backend | Laravel Fortify |
| Roles & Permissions | spatie/laravel-permission |
| Audit Logging | spatie/laravel-activitylog |
| Database | MySQL 8 |
| Code Quality | Laravel Pint (PSR-12) + PHPStan |

### Frontend
| Layer | Technology |
|---|---|
| Bridge | **Inertia.js v2** — Laravel ↔ React, no separate API needed |
| Framework | **React 19** |
| Language | **TypeScript 5.x** (strict mode) |
| Styling | **Tailwind CSS v4** |
| UI Components | **shadcn/ui** (Radix UI primitives) |
| Icons | Lucide React |
| Forms | React Hook Form + Zod (client-side) + Laravel FormRequest (server-side) |
| Build | Vite 7 |

### Why This Stack
- **Laravel 13 React starter kit** ships shadcn/ui pre-configured — minimal setup
- **Inertia.js** avoids separate REST API — Laravel sends props directly to React pages
- **TypeScript** adds compile-time safety across the full stack
- **shadcn/ui** gives polished, accessible components without pulling in a UI framework
- **Dual-skill signal** — client sees both PHP backend craftsmanship AND modern React/TS frontend

## Panel Architecture (Role-Based)

The application has **two separate panels**, each with its own layout,
route group, middleware, and Livewire namespace:

| Panel | URL Prefix | Role Required | Purpose |
|---|---|---|---|
| **Super Admin** | `/admin/*` | `super_admin` | Manage all clients, users, incidents, audit logs, system settings |
| **Client Portal** | `/portal/*` | `client` | Clients see only their own data: contacts, incidents, team |

Both panels share: auth, 2FA, security headers, audit logging.
Each panel has: own sidebar, own dashboard, scoped data queries.

---

## Modules

### 1. Auth & Security Setup
- Email/password login with Fortify
- Two-Factor Authentication (TOTP — Google Authenticator / Authy)
- Login lockout after 5 failed attempts (RateLimiter)
- Email verification on registration
- Password reset with single-use expiring token (60 min)
- Forced password expiry after 90 days (`password_changed_at`)
- Password strength policy (min 8, uppercase, number, symbol)
- Remember Me with secure HttpOnly cookie
- Session regeneration on login
- Security headers middleware (CSP, HSTS, X-Frame-Options, X-Content-Type-Options)
- Honeypot field on all public forms

### 2. Clients (Companies)
- Full CRUD with soft delete
- Search, filter, pagination via Livewire
- **Security Score badge** — calculated from open incidents + unverified contacts
- Assign owner/account manager (with role check)
- Activity log on every change

### 3. Contacts (People)
- Belongs to a Client
- Full CRUD
- **Sensitive data masking** — phone/email shown as `+1 **** 4532` in list view, full on detail
- Role-gated: only Manager+ can see unmasked data
- Soft delete

### 4. Incidents (Security Cases)
- Status workflow: `open → in_progress → resolved → closed`
- Priority levels: `low | medium | high | critical`
- Assigned to internal user
- Audit trail: every status/priority change logged with actor + timestamp
- Filter by status, priority, assigned user, date range
- Email notification on assignment

### 5. Security Center
- **Active Sessions** — list all logged-in devices/IPs, revoke any session
- **Audit Log Viewer** — paginated log of all system actions (who, what, when, IP)
- **Suspicious Login Alerts** — email notification on login from new IP
- **Admin Force-Logout** — admin can invalidate any user's active session

---

## Security Features Summary

| Feature | Implementation |
|---|---|
| Login lockout | Fortify + Laravel RateLimiter (5 attempts → 15 min block) |
| Two-Factor Auth | Fortify TOTP (RFC 6238) |
| CSRF protection | Laravel default on all forms (shown explicitly) |
| Only validated data to DB | `$request->validated()` enforced — never `$request->all()` |
| Mass assignment protection | `$fillable` on every model — no `$guarded = []` shortcuts |
| Password hashing | bcrypt (cost 12) |
| SQL injection prevention | Eloquent ORM only — no raw query strings |
| XSS prevention | Blade `{{ }}` escaping throughout — no `{!! !!}` without sanitization |
| Role-based access control | spatie/laravel-permission + Laravel Policies |
| Audit logging | spatie/laravel-activitylog on every model |
| Security headers | Custom middleware: CSP, HSTS, X-Frame-Options, Referrer-Policy |
| Honeypot | Hidden field on forms — bot detection without CAPTCHA |
| Password policy | Custom validation rule — length, complexity |
| Password expiry | `password_changed_at` checked on every login |
| Single-use reset token | Marked consumed immediately, 60 min expiry |
| Session management | Active sessions UI, admin force-logout |
| Suspicious login alert | IP comparison on login, email alert on mismatch |
| Sensitive data masking | Phone/email masked in list views by default |
| Secure cookies | `HttpOnly`, `Secure`, `SameSite=Strict` flags |

---

## Architecture

```
app/
├── Http/
│   ├── Controllers/          # Thin — delegate to Services
│   ├── Requests/             # FormRequest for every form
│   ├── Resources/            # API response formatting
│   └── Middleware/
│       └── SecurityHeaders.php
├── Services/
│   ├── ClientService.php
│   ├── ContactService.php
│   ├── IncidentService.php
│   └── SecurityScoreService.php
├── Models/
│   ├── User.php
│   ├── Client.php
│   ├── Contact.php
│   └── Incident.php
├── Enums/
│   ├── IncidentStatus.php
│   └── IncidentPriority.php
├── Policies/
│   ├── ClientPolicy.php
│   ├── ContactPolicy.php
│   └── IncidentPolicy.php
└── Livewire/
    ├── Auth/
    ├── Clients/
    │   ├── ClientIndex.php
    │   ├── ClientForm.php
    │   └── ClientShow.php
    ├── Contacts/
    ├── Incidents/
    └── SecurityCenter/
        ├── SessionManager.php
        └── AuditLogViewer.php

resources/
└── views/
    ├── layouts/
    │   ├── app.blade.php
    │   └── auth.blade.php
    ├── components/        # Blade components (cards, badges, alerts)
    └── livewire/          # Livewire views
```

---

## Coding Standards

- **Controllers:** max ~30 lines — validate, call service, return response
- **Services:** all business logic, no direct Request objects
- **FormRequests:** every create/update action has its own request class
- **Enums:** PHP 8.1 backed enums for all status/type fields
- **Scopes:** Eloquent local scopes for all common filters (`scopeOpen`, `scopeCritical`)
- **Casts:** proper casting in models — enums, dates, booleans
- **Comments:** only where WHY is non-obvious — no narrating what code does
- **Migrations:** proper indexes, foreign keys, `onDelete('cascade')` where appropriate
- **Soft Deletes:** on all main models
- **No magic strings:** enums or constants, never inline `'open'`, `'high'`

---

## Branch Strategy — Full Git Flow

Standard Git Flow with 5 branch types:

```
main              # Production — tagged releases only (v1.0.0, v1.1.0, …)
└── develop       # Integration — all feature work merges here first
    ├── feature/project-setup
    ├── feature/auth-security
    ├── feature/admin-panel
    ├── feature/client-portal
    ├── feature/clients-module
    ├── feature/contacts-module
    ├── feature/incidents-module
    └── feature/security-center

release/v1.0.0    # Branched from develop when ready to ship
                  # Bug fixes only, no new features
                  # Merged to BOTH main (with tag) AND back to develop

hotfix/*          # Branched from main for urgent production fixes
                  # Merged to BOTH main (with tag) AND back to develop
```

### Branch Rules
| Branch type | Branched from | Merges into | Naming |
|---|---|---|---|
| `feature/*` | `develop` | `develop` (via PR) | `feature/short-name` |
| `release/*` | `develop` | `main` + `develop` | `release/v1.0.0` |
| `hotfix/*` | `main` | `main` + `develop` | `hotfix/v1.0.1-fix-login` |

### Commit Convention
Follows **Conventional Commits**:
```
feat(auth): add two-factor authentication via Fortify
fix(clients): prevent duplicate email on client creation
refactor(services): extract AuditLogger into interface
test(incidents): add status transition test cases
docs(readme): document local setup steps
chore(deps): upgrade spatie/laravel-permission to v6.2
```

### PR Workflow
1. Branch from `develop` → do work → push → open PR against `develop`
2. PR description includes: What, Why, How to test
3. Merge via **Squash & Merge** (keeps history clean)
4. Delete feature branch after merge
5. When all features done → create `release/v1.0.0` → PR to `main` → tag release

---

## Packages

### Composer (Backend)
```json
{
    "require": {
        "php": "^8.4",
        "laravel/framework": "^13.0",
        "laravel/fortify": "^1.x",
        "inertiajs/inertia-laravel": "^2.0",
        "spatie/laravel-permission": "^6.x",
        "spatie/laravel-activitylog": "^4.x",
        "tightenco/ziggy": "^2.x"
    },
    "require-dev": {
        "laravel/pint": "^1.x",
        "larastan/larastan": "^3.x",
        "pestphp/pest": "^3.x",
        "pestphp/pest-plugin-laravel": "^3.x"
    }
}
```

### NPM (Frontend)
```json
{
    "dependencies": {
        "@inertiajs/react": "^2.0",
        "react": "^19.0",
        "react-dom": "^19.0",
        "react-hook-form": "^7.x",
        "zod": "^3.x",
        "@hookform/resolvers": "^3.x",
        "lucide-react": "^0.x",
        "clsx": "^2.x",
        "tailwind-merge": "^2.x",
        "class-variance-authority": "^0.7.x",
        "@radix-ui/react-dialog": "^1.x",
        "@radix-ui/react-dropdown-menu": "^2.x",
        "@radix-ui/react-select": "^2.x",
        "@radix-ui/react-toast": "^1.x",
        "ziggy-js": "^2.x"
    },
    "devDependencies": {
        "@types/react": "^19.0",
        "@types/react-dom": "^19.0",
        "@types/node": "^22.0",
        "typescript": "^5.x",
        "vite": "^7.x",
        "@vitejs/plugin-react": "^4.x",
        "laravel-vite-plugin": "^2.x",
        "tailwindcss": "^4.x",
        "@tailwindcss/vite": "^4.x",
        "eslint": "^9.x",
        "prettier": "^3.x",
        "prettier-plugin-tailwindcss": "^0.x"
    }
}
```

---

## Case Study Highlights (post-development)

Document these after build for the case study:

1. **Security-first architecture** — every form uses `$request->validated()`, never raw input
2. **Zero magic strings** — PHP 8.1 enums for all statuses/priorities
3. **Audit trail by design** — every model change logged with actor, timestamp, IP
4. **Layered auth security** — 6 independent security controls on the login flow alone
5. **Reactive UI without JavaScript overhead** — Livewire components keep all logic server-side
6. **RBAC at every layer** — route middleware + controller policies + view gates
7. **Security Score** — domain-aware feature showing understanding of cybersecurity context
8. **Sensitive data masking** — PII protected in list views by default, role-gated reveal
9. **Session transparency** — users see and control their own active sessions
10. **Security headers** — single middleware covers OWASP recommended headers

---

## Development Order

1. `feature/project-setup` — Laravel 13 + Inertia + React + TS + shadcn/ui scaffold
2. `feature/auth-security` — Fortify, 2FA, lockout, security middleware, password policy
3. `feature/admin-panel` — Super admin layout, sidebar, dashboard shell (React)
4. `feature/client-portal` — Client portal layout, sidebar, dashboard shell (React)
5. `feature/clients-module` — Full Client CRUD (admin only) + Security Score
6. `feature/contacts-module` — Contacts CRUD + data masking (both panels, scoped)
7. `feature/incidents-module` — Incident workflow + audit log (both panels, scoped)
8. `feature/security-center` — Session manager + audit log viewer
9. `release/v1.0.0` — Final polish, README, demo data seeder, tag release

---

*Plan last updated: October 2026*
