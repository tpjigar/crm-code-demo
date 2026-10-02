# Branch & Commit Plan

> Granular commit roadmap per feature branch.
> Each commit is **small, scoped, and conventionally named**.
> You create the branch & commits in JetBrains; I provide code and commit messages.

---

## Git Flow Setup (One-Time)

```bash
git init
git branch -M main
# Make first commit (project scaffold)
git checkout -b develop
```

Then always:
- Branch from `develop`
- PR to `develop`
- Delete branch after squash & merge

---

## Branch 1 — `feature/project-setup`

**Base:** `develop`
**Goal:** Working Laravel 13 + Inertia + React + TS + shadcn/ui skeleton.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `chore: initial Laravel 13 project scaffold` | `composer create-project` output |
| 2 | `chore(deps): install inertia-laravel + ziggy` | Composer |
| 3 | `chore(deps): install react, inertia/react, typescript` | NPM |
| 4 | `chore(deps): install tailwind v4 + shadcn tooling` | NPM |
| 5 | `feat(setup): configure vite for react + typescript` | `vite.config.ts`, `tsconfig.json` |
| 6 | `feat(setup): add inertia root blade + ssr entry` | `app.blade.php`, `app.tsx`, `ssr.tsx` |
| 7 | `feat(setup): initialize shadcn/ui + base components` | Button, Input, Dialog, Form |
| 8 | `feat(setup): configure tailwind v4 + shadcn tokens` | `app.css`, theme config |
| 9 | `feat(setup): add HandleInertiaRequests middleware` | Shared props (auth, flash) |
| 10 | `feat(setup): configure mysql + .env.example` | Database config |
| 11 | `chore(deps): install spatie/permission + activitylog + fortify` | Composer |
| 12 | `feat(setup): add Pint, PHPStan, ESLint, Prettier configs` | Code quality tooling |
| 13 | `docs: add README with setup instructions` | README |

**PR title:** `feat(setup): Laravel 13 + Inertia + React + shadcn/ui scaffold`

---

## Branch 2 — `feature/auth-security`

**Base:** `develop`
**Goal:** Hardened auth system with 2FA, lockout, policies, security middleware.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(auth): publish fortify config + service provider` | Fortify setup |
| 2 | `feat(auth): add two-factor columns migration` | Migration |
| 3 | `feat(auth): add password_changed_at + last_login_ip columns` | Migration |
| 4 | `feat(auth): create UserRole enum (super_admin, client)` | Enum |
| 5 | `feat(auth): seed roles and permissions` | Seeder |
| 6 | `feat(auth): custom login FormRequest with lockout` | LoginRequest, action |
| 7 | `feat(auth): build Login + 2FA React pages with shadcn` | Pages, Zod schema |
| 8 | `feat(auth): custom password strength rule (StrongPasswordRule)` | Rule |
| 9 | `feat(auth): password expiry middleware (90-day policy)` | Middleware |
| 10 | `feat(security): SecurityHeaders middleware (CSP, HSTS, X-Frame)` | Middleware |
| 11 | `feat(security): honeypot field + validation on public forms` | Honeypot |
| 12 | `feat(auth): EnsureSuperAdmin + EnsureClientUser middleware` | Role guards |
| 13 | `feat(auth): login redirect by role (admin vs portal)` | LoginController |
| 14 | `feat(auth): suspicious login detector + email notification` | Event, Listener, Notification |
| 15 | `test(auth): login lockout, 2FA, password policy tests` | Pest tests |

**PR title:** `feat(auth): enterprise auth with 2FA, lockout, policies, security headers`

---

## Branch 3 — `feature/admin-panel`

**Base:** `develop`
**Goal:** Super Admin layout shell with sidebar, dashboard, nav.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(admin): add admin.php route file + register in bootstrap` | Routes |
| 2 | `feat(admin): AdminLayout.tsx with sidebar + topbar` | Layout |
| 3 | `feat(admin): sidebar nav with role-based links` | Nav component |
| 4 | `feat(admin): empty Dashboard.tsx shell` | Dashboard page |
| 5 | `feat(admin): DashboardController with Inertia render` | Controller |
| 6 | `feat(admin): add admin user dropdown (profile, logout)` | UI |
| 7 | `test(admin): guest cannot access, client cannot access` | Pest tests |

**PR title:** `feat(admin): super admin panel layout + dashboard shell`

---

## Branch 4 — `feature/client-portal`

**Base:** `develop`
**Goal:** Client Portal layout shell with sidebar, dashboard, nav.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(portal): add portal.php route file + register` | Routes |
| 2 | `feat(portal): PortalLayout.tsx with sidebar + topbar` | Layout |
| 3 | `feat(portal): sidebar nav for client-level actions` | Nav component |
| 4 | `feat(portal): empty client Dashboard.tsx shell` | Dashboard page |
| 5 | `feat(portal): DashboardController scoped to client user` | Controller |
| 6 | `feat(portal): ClientTenantScope global eloquent scope` | Scope class |
| 7 | `test(portal): data scoping prevents cross-client leak` | Pest tests |

**PR title:** `feat(portal): client portal layout + tenant-scoped dashboard`

---

## Branch 5 — `feature/clients-module`

**Base:** `develop`
**Goal:** Full Client CRUD (admin only) with Security Score.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(clients): create clients migration + model` | Migration, Model |
| 2 | `feat(clients): ClientFactory + ClientSeeder` | Testing data |
| 3 | `feat(clients): ClientData DTO + ClientPolicy` | DTO, Policy |
| 4 | `feat(clients): ClientService + ClientServiceInterface` | Service layer |
| 5 | `feat(clients): CreateClientAction + UpdateClientAction + DeleteClientAction` | Actions |
| 6 | `feat(clients): StoreClientRequest + UpdateClientRequest` | FormRequests |
| 7 | `feat(clients): ClientResource for Inertia props` | Resource |
| 8 | `feat(clients): Admin ClientController (CRUD routes)` | Controller |
| 9 | `feat(clients): Admin/Clients/Index.tsx with DataTable + search` | React page |
| 10 | `feat(clients): Admin/Clients/Create.tsx + Edit.tsx with RHF + Zod` | React pages |
| 11 | `feat(clients): Admin/Clients/Show.tsx with detail view` | React page |
| 12 | `feat(clients): ClientObserver for auto audit logging` | Observer |
| 13 | `feat(clients): SecurityScoreService + score computation` | Service |
| 14 | `feat(clients): SecurityScoreCard component on client detail` | React component |
| 15 | `test(clients): full CRUD + policy + security score tests` | Pest tests |

**PR title:** `feat(clients): full Client CRUD with Security Score + audit logging`

---

## Branch 6 — `feature/contacts-module`

**Base:** `develop`
**Goal:** Contacts CRUD across both panels, with data masking.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(contacts): create contacts migration + model` | Migration, Model |
| 2 | `feat(contacts): ContactFactory + ContactSeeder` | Testing data |
| 3 | `feat(contacts): ContactData DTO + ContactPolicy` | DTO, Policy |
| 4 | `feat(contacts): ContactService + interface + actions` | Service, Actions |
| 5 | `feat(contacts): StoreContactRequest + UpdateContactRequest` | FormRequests |
| 6 | `feat(contacts): SensitiveDataMaskingService + DataMasker` | Masking logic |
| 7 | `feat(contacts): ContactResource with role-based masking` | Resource |
| 8 | `feat(contacts): Admin ContactController + React pages` | Admin side |
| 9 | `feat(contacts): Portal ContactController + React pages (scoped)` | Portal side |
| 10 | `feat(contacts): MaskedField shared component (click to reveal)` | React component |
| 11 | `test(contacts): CRUD, masking, cross-client access blocked` | Pest tests |

**PR title:** `feat(contacts): nested Contact CRUD with sensitive data masking`

---

## Branch 7 — `feature/incidents-module`

**Base:** `develop`
**Goal:** Incident workflow with status enum, assignment, audit log.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(incidents): IncidentStatus + IncidentPriority enums` | Enums |
| 2 | `feat(incidents): create incidents migration + model + casts` | Migration, Model |
| 3 | `feat(incidents): IncidentFactory + IncidentSeeder` | Testing data |
| 4 | `feat(incidents): IncidentData DTO + IncidentPolicy` | DTO, Policy |
| 5 | `feat(incidents): IncidentService + interface` | Service |
| 6 | `feat(incidents): Create + Update + Delete actions` | Actions |
| 7 | `feat(incidents): TransitionIncidentStatusAction (state machine)` | Workflow action |
| 8 | `feat(incidents): AssignIncidentAction + email notification` | Action, Notification |
| 9 | `feat(incidents): StoreIncidentRequest + status/priority rules` | FormRequests |
| 10 | `feat(incidents): IncidentResource` | Resource |
| 11 | `feat(incidents): Admin IncidentController + React pages (full)` | Admin side |
| 12 | `feat(incidents): Portal IncidentController + React pages (scoped)` | Portal side |
| 13 | `feat(incidents): StatusBadge + PriorityBadge shared components` | React |
| 14 | `feat(incidents): IncidentObserver with status transition logging` | Observer |
| 15 | `test(incidents): status workflow, assignment, audit, scoping` | Pest tests |

**PR title:** `feat(incidents): incident workflow with status transitions + audit`

---

## Branch 8 — `feature/security-center`

**Base:** `develop`
**Goal:** Active sessions management + Audit log viewer.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `feat(security): create user_sessions migration + model` | Migration, Model |
| 2 | `feat(security): SessionManagerService + interface` | Service |
| 3 | `feat(security): track session on login (Fortify hook)` | Listener |
| 4 | `feat(security): RevokeSessionAction + ForceLogoutUserAction` | Actions |
| 5 | `feat(security): SessionController for both panels` | Controllers |
| 6 | `feat(security): ActiveSessionsList shared component` | React component |
| 7 | `feat(security): AuditLogController (admin only) + policy` | Controller, Policy |
| 8 | `feat(security): Admin/Security/AuditLog.tsx with filters + pagination` | React page |
| 9 | `feat(security): Admin/Security/ActiveSessions.tsx (all users)` | React page |
| 10 | `feat(security): Portal profile page shows own sessions` | React page |
| 11 | `test(security): session revoke, audit viewer, force logout` | Pest tests |

**PR title:** `feat(security): session management + audit log viewer`

---

## Release — `release/v1.0.0`

**Base:** `develop`
**Goal:** Polish, demo data, docs, tag.

| # | Commit Message | Scope |
|---|---|---|
| 1 | `chore: branch release/v1.0.0` | Branch cut |
| 2 | `feat(seeder): DemoDataSeeder with realistic sample data` | Seeder |
| 3 | `docs: expand README with screenshots + feature list` | README |
| 4 | `docs: add CASE_STUDY.md highlighting features + decisions` | Case study |
| 5 | `fix: minor polish from review (TBD)` | Polish |
| 6 | `chore(release): bump version to 1.0.0` | Version |

**Then:**
- Merge `release/v1.0.0` → `main` with tag `v1.0.0`
- Merge `release/v1.0.0` → `develop` (back-merge)

---

## Summary

| Branch | Est. Commits | PR |
|---|---|---|
| `feature/project-setup` | 13 | → develop |
| `feature/auth-security` | 15 | → develop |
| `feature/admin-panel` | 7 | → develop |
| `feature/client-portal` | 7 | → develop |
| `feature/clients-module` | 15 | → develop |
| `feature/contacts-module` | 11 | → develop |
| `feature/incidents-module` | 15 | → develop |
| `feature/security-center` | 11 | → develop |
| `release/v1.0.0` | 6 | → main (+back to develop) |

**Total: ~100 commits across 9 branches, 8 PRs + 1 release**

---

## Workflow Per Feature

For each feature branch, our loop will be:

1. **I:** write code for a logical chunk (1 commit scope)
2. **I:** tell you the exact commit message + files changed
3. **You:** review in JetBrains → stage files → commit with the given message
4. Repeat until branch is done
5. **You:** push branch, open PR to `develop` with my suggested PR description
6. **You:** merge via Squash & Merge → delete branch
7. Pull `develop` locally → start next branch

Simple, repeatable, you keep full control of the git history.

---

*Branch & commit plan last updated: October 2026*
