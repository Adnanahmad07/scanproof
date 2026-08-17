# ScanProof — Context & Current State

> **Single source of truth** for AI coding agents continuing ScanProof development.
> Last updated: 2026-06-12. Read fully before writing any code.
> **Workflow rule: work feature by feature. A feature is DONE only when its test cases pass.**

---

## 1. Product Summary

ScanProof is a Facility Operations Platform (cleaning, maintenance, facility management).
Every room/location gets a printed QR code. Anyone scans the QR and reports a problem.
Supervisors convert reports into tasks and assign staff. Staff fix the issue and upload
**before + after photos as proof**. Admins get analytics and automated reports.

**Core value:** zero-friction issue reporting + photographic proof of completion.

---

## 2. Tech Stack (actual — do not change)

| Layer | Technology | Version |
|---|---|---|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Frontend | Livewire | 4.3+ |
| CSS | Tailwind CSS | 4.0+ (`@tailwindcss/vite`) |
| Build | Vite | 7.0+ |
| Auth | Laravel Fortify | 1.37+ |
| Testing | PHPUnit | 11.5+ |
| DB (prod) | MySQL | — |
| DB (test) | SQLite in-memory | — |

> Note: the original plan said Laravel 11 + Breeze. The project was actually built on
> **Laravel 12 + Fortify**. Fortify is the truth — do not migrate to Breeze.

---

## 3. Roles

`app/Enums/UserRole.php` — 4 roles (enum column on `users`, default `staff`):

| Role | Dashboard | Status |
|---|---|---|
| Admin | `/admin/dashboard` | ✅ Implemented |
| Supervisor | `/supervisor/dashboard` | ✅ Implemented |
| Staff | `/staff/dashboard` | ✅ Implemented |
| Client | `/client/dashboard` | ❌ Not implemented |

Middleware: `app/Http/Middleware/CheckRole.php`, registered as `role` in `bootstrap/app.php`.

---

## 4. CURRENT STATE — What Is Done (Phase 1)

### 4.1 Landing Page ✅
- `resources/views/pages/landing.blade.php` + components: `nav`, `hero`, `features` (6), `cta`, `footer`
- Route: `GET /`

### 4.2 Authentication (Fortify) ✅
- Views: `auth/login`, `auth/register`, `auth/forgot-password`, `auth/reset-password`, `auth/verify-email`
- Custom `POST /logout` (clears session → redirects `/`)
- Config: `config/fortify.php`, `app/Providers/FortifyServiceProvider.php`

### 4.3 Dashboards ✅
- Admin: `layouts/admin.blade.php` (292 lines) + `pages/admin/dashboard.blade.php` (stats placeholders "--")
- Supervisor: `layouts/supervisor.blade.php` (164 lines) + `pages/supervisor/dashboard.blade.php`
- Auth layout: `layouts/auth.blade.php` (112 lines, split-screen)

### 4.4 Supervisor Invitation Flow ✅ (bonus, beyond Phase 1)
- `app/Models/SupervisorInvitation.php` — token generation, expiry, status (`isExpired()`, `isAccepted()`, `isValidForUse()`, `markAccepted()`, `markExpired()`, scopes: `pending`, `expired`, `valid`)
- `app/Http/Controllers/Admin/SupervisorInvitationController.php`
- `app/Livewire/Admin/SupervisorManagement.php` (invite, cancel, demote)
- `app/Livewire/Supervisor/SetPassword.php` (token link)
- `app/Mail/SupervisorInvitationMail.php` + `emails/supervisor-invitation.blade.php`
- `app/Http/Middleware/ValidateInvitationToken.php`

### 4.5 Database ✅
- 7 migrations: `users` (role + 2FA columns), `password_reset_tokens`, `sessions`, `cache`, `jobs`, `passkeys`, `supervisor_invitations`
- Seeder: `admin@scanproof.com` / `password` (Admin)

**users:** id, name, email (unique), role (enum, default staff), email_verified_at, password, two_factor_*, remember_token, timestamps
**supervisor_invitations:** id, email, token (64, unique), invited_by FK→users, expires_at, accepted_at, status (pending/accepted/expired), timestamps

### 4.6 Routes (implemented)

| Method | URI | Middleware |
|---|---|---|
| GET | `/` | none |
| GET/POST | `/login`, `/register`, `/forgot-password` | guest (Fortify) |
| GET | `/reset-password/{token}` · POST `/reset-password` | guest |
| GET | `/email/verify` | auth |
| POST | `/logout` | auth |
| GET | `/dashboard` | auth (role-based redirect) |
| GET | `/admin`, `/admin/dashboard` | auth, role:admin |
| GET | `/admin/supervisors` · POST `.../invite` · DELETE `.../{invitation}` | auth, role:admin |
| GET | `/supervisor/dashboard` | auth, role:supervisor |
| GET | `/supervisor/set-password/{token}` | ValidateInvitationToken |

### 4.7 Tests (current)
- Feature: `ExampleTest` (1 ✅), `AuthRoutesTest` (13 ✅), `AuthenticationTest` (14, partial), `PasswordResetTest` (7, partial), `SupervisorInvitationTest` (9), + 3 debug test files (`FormSubmissionDebugTest`, `FormSubmissionFixedTest`, `FortifyActionHandlerDebugTest`)
- Unit: `SupervisorInvitationTest` (9)

### 4.8 Theme & Build ✅
- `resources/css/app.css` — custom Tailwind tokens (primary, success, warning, error, info, surface, text, sidebar). Font: Inter (Bunny Fonts)
- Vite assets compiled in `public/build/`
- Composer scripts: `setup`, `dev`, `test`

---

## 5. FIX FIRST — Known Issues (each with a test case)

Do these **before** any new feature. Each fix = code + passing test.

| # | Issue | Fix | Test case |
|---|---|---|---|
| KI-1 | `CreateNewUser` defaults every registration to **Admin** (security bug) | Default new registrations to a safe role (or block open registration) | **TC-KI.1:** registering via `/register` never produces an admin user |
| KI-2 | `RoleRedirectMiddleware` defined but **not registered** | Register it in `bootstrap/app.php` or delete it | **TC-KI.2:** `GET /dashboard` redirects each role to its own dashboard |
| KI-3 | POST to `/register`, `/login`, `/forgot-password` fail in tests (CSRF/Fortify integration) | Diagnose and fix; make `AuthenticationTest` + `PasswordResetTest` fully green | **TC-KI.3:** all tests in `AuthenticationTest` and `PasswordResetTest` pass |
| KI-4 | `dashboard.blade.php` (307 lines, hardcoded dummy data) unused | Delete or repurpose; nothing >300 lines | **TC-KI.4:** test suite still green after removal |
| KI-5 | `welcome.blade.php` unused default | Delete | — |
| KI-6 | 3 debug test files clutter the suite | Remove debug tests once KI-3 is fixed | — |

### Missing Phase 1 tests (complete these too)
- **TC-1.1** Landing page displays (200, key content)
- **TC-1.2** Landing page navigation links work
- **TC-1.6** Admin dashboard access control (guest → login; non-admin → 403/redirect)
- **TC-1.7** Admin dashboard content renders
- **TC-1.8** Admin layout renders (sidebar, nav)
- **TC-1.9** Guest/auth layout renders
- **TC-1.10** Database connection / migrations run

**Phase 1 exit criteria:** all KI fixes done + TC-1.x written + full suite green.

---

## 6. ROADMAP — Feature by Feature (each with test cases)

> Rule: pick ONE feature, implement it, write its test cases, get them green, then move on.
> No phase N+1 work until phase N is fully green.

### Phase 2 — User Management
| Feature | Test cases |
|---|---|
| F2.1 User CRUD (admin lists/creates/edits/deactivates users) | TC-2.1 admin lists users · TC-2.2 admin creates user with role · TC-2.3 admin edits user · TC-2.4 admin deactivates user (blocked from login) · TC-2.5 non-admin gets 403 on all user CRUD routes |
| F2.2 Role assignment | TC-2.6 admin changes a user's role; `/dashboard` redirect follows new role · TC-2.7 user cannot change own role |
| F2.3 Profile management | TC-2.8 user updates name/email · TC-2.9 password change requires current password · TC-2.10 invalid input rejected with validation errors |
| F2.4 Staff & Client dashboards (stubs) | TC-2.11 staff sees `/staff/dashboard`, others 403 · TC-2.12 client sees `/client/dashboard`, others 403 |

### Phase 3 — Locations & QR
| Feature | Test cases |
|---|---|
| F3.1 Location CRUD (name, building, floor, notes, uuid) | TC-3.1 CRUD works for admin · TC-3.2 duplicate (name+building+floor) rejected · TC-3.3 location gets a uuid on create |
| F3.2 QR code generation | TC-3.4 QR encodes `/r/{uuid}` (never numeric id) · TC-3.5 printable QR PDF/sheet generated for selected locations |
| F3.3 QR scan endpoint | TC-3.6 `GET /r/{uuid}` resolves location (200) · TC-3.7 invalid/unknown uuid → 404 |
| F3.4 Location history | TC-3.8 scans/visits recorded with timestamp · TC-3.9 history visible per location |

### Phase 4 — Tasks
| Feature | Test cases |
|---|---|
| F4.1 Task CRUD | TC-4.1 supervisor creates task (location, category, priority, due date) · TC-4.2 staff cannot create tasks |
| F4.2 Task assignment | TC-4.3 supervisor assigns staff; staff sees it in "my tasks" · TC-4.4 staff cannot see others' tasks |
| F4.3 Status workflow (`assigned → in_progress → done → verified`, plus `blocked`, `reopened`) | TC-4.5 each valid transition succeeds · TC-4.6 invalid transitions rejected · TC-4.7 every transition writes an audit/event row |
| F4.4 Photo upload (before/after proof) | TC-4.8 before photo required to start, after photo required to finish · TC-4.9 mime (jpeg/png/webp) + max 5MB validated · TC-4.10 photos stored per task with type |

### Phase 5 — Issues (public reporting)
| Feature | Test cases |
|---|---|
| F5.1 Public QR issue report (`/r/{uuid}` form) | TC-5.1 anonymous submit creates issue (status reported) · TC-5.2 description min length validated · TC-5.3 honeypot/rate-limit blocks spam |
| F5.2 Tracking code | TC-5.4 unique `SP-XXXXXX` code generated · TC-5.5 `/track` shows status timeline only (no internal data) |
| F5.3 Issue routing | TC-5.6 new issue appears in supervisor feed · TC-5.7 supervisor converts issue → task / rejects with reason |
| F5.4 Issue tracking | TC-5.8 status changes appear in public timeline |

### Phase 6 — Reports & Analytics
| Feature | Test cases |
|---|---|
| F6.1 Dashboard analytics (replace "--" placeholders) | TC-6.1 stats computed correctly from seeded data (open vs closed, avg resolution time, top locations, by category) |
| F6.2 Report generation + PDF export | TC-6.2 report builds for a date range · TC-6.3 PDF downloads (200, correct headers) |
| F6.3 Facility health score | TC-6.4 score formula returns expected value for known fixture data |

### Phase 7 — Notifications
| Feature | Test cases |
|---|---|
| F7.1 Email notifications (assignment, completion, etc.) | TC-7.1 mail queued on event (Mail::fake) |
| F7.2 WhatsApp integration | TC-7.2 message dispatched via faked client |
| F7.3 Automated daily reports | TC-7.3 scheduled job generates + sends daily report |

### Phase 8 — Advanced
| Feature | Test cases |
|---|---|
| F8.1 GPS verification | TC-8.1 scan outside geofence flagged |
| F8.2 SLA management | TC-8.2 overdue task triggers SLA breach |
| F8.3 Client portal | TC-8.3 client sees only own facility's data |
| F8.4 Building map view | TC-8.4 map renders locations with status |

---

## 7. Development Rules

1. **Test-driven:** every feature ships with its TC-x tests, all green, before moving on.
2. **No file exceeds 300 lines** — split into components/services/traits.
3. **Modular structure** by feature/domain (follow existing `app/Livewire/Admin`, `app/Livewire/Supervisor` pattern).
4. **Phase-gated:** no Phase N+1 until Phase N is fully green.
5. **Status transitions** centralized in a service (never set status directly from components); every transition writes an event row.
6. **QR URLs use uuid**, never auto-increment ids.
7. Reuse existing patterns: Fortify actions, `role` middleware, invitation-token flow, custom Tailwind tokens in `app.css`.

---

## 8. Out of Scope (do not build)

Payments/billing · websockets (use `wire:poll`) · native mobile apps (mobile-first web only) · push/SMS (email + WhatsApp per Phase 7 only) · i18n · shift scheduling · inventory/asset management · SSO · advanced charts/exports · issue merging UI.

---

## 9. Quick Commands

```bash
composer setup        # full project setup
composer dev          # server + queue + pail + vite
composer test         # clear config + run tests
php artisan test --filter=AuthRoutesTest
npm run build
php artisan migrate && php artisan db:seed
```

---

## 10. Where to Start (next session)

1. Fix **KI-1** (admin-by-default registration) — highest risk.
2. Fix **KI-3** (form submission test failures) — unblocks the whole suite.
3. Resolve **KI-2, KI-4, KI-5, KI-6** (cleanup).
4. Write missing **TC-1.x** tests → Phase 1 fully green.
5. Start **Phase 2, F2.1 User CRUD** with TC-2.1 → TC-2.5.
