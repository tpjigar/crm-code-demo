# Changelog

All notable changes to this project are documented here. The format
follows [Keep a Changelog](https://keepachangelog.com/en/1.1.0/) and
versions follow [Semantic Versioning](https://semver.org/).

## [1.0.0] — 2026-10-03

Initial public release of the CRM Code Demo. Everything below shipped as
part of a disciplined Git Flow sequence (`feature → develop → main →
production`, 23 merged PRs, zero branches deleted).

### Added
- **Authentication & security** — Laravel Fortify login, TOTP 2FA,
  5-attempt lockout, `StrongPasswordRule` (12 chars, mixed case, number,
  symbol), `EnsurePasswordNotExpired` 90-day middleware,
  `SecurityHeaders` (HSTS/CSP/X-Frame/Referrer-Policy), suspicious-login
  detection with queued email notification, role-based login redirect.
- **RBAC** — Spatie Permission with `UserRole` enum (`super_admin`,
  `client`) and seeded permission matrix.
- **Multi-tenancy** — `ClientTenantScope` global Eloquent scope with
  policy-level same-tenant defense in depth.
- **Clients module** — Full REST CRUD, soft deletes, owner assignment,
  Security Score + `ClientRiskLevel` enum, Spatie ActivityLog via
  `ClientObserver`.
- **Contacts module** — Full REST CRUD, server-side PII masking
  (`SensitiveDataMasker`), primary-contact invariant in DB transaction,
  portal read-only view with `MaskedField` React component.
- **Incidents module** — Full REST CRUD, `IncidentStatus` state machine
  (`new → investigating → resolved → closed`, re-open allowed),
  `IncidentWorkflowService` guards illegal transitions,
  `IncidentSeverity` enum with ISO/IEC 27035 ack SLAs, portal
  self-service reporting with forged-payload defense.
- **Security Center** — `SessionManager` with per-session revoke
  (self-session excluded), `UserAgentParser`, `AuditLogService` wrapping
  Spatie ActivityLog with 5-filter viewer and JSON diff drawer.
- **Admin UI** — Super Admin layout with sidebar, breadcrumbs, KPI
  dashboard, modular routes under `routes/admin.php`.
- **Portal UI** — Client Portal layout and dashboard, routes under
  `routes/portal.php`.
- **DemoDataSeeder** — 8 clients, 34 contacts, 20 incidents, 8 portal
  users for an instant walkthrough after `php artisan migrate --seed`.
- **Docs** — `README.md` with feature matrix, `docs/CASE_STUDY.md`
  engineering walkthrough, `docs/ARCHITECTURE.md`, `docs/PLAN.md`,
  `docs/BRANCH_COMMIT_PLAN.md`.

### Quality
- **136 Pest tests** pass with 487 assertions.
- **PHPStan level 6** (Larastan) — 0 errors.
- **Laravel Pint** — clean, strict-types enforced.
- **TypeScript 5 strict** — clean.
- **Vite build** completes in ~2.5s.
