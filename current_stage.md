# ScanProof - Current Project State

> **Purpose:** This file documents the exact state of the project so another AI (Claude Fable 5) can pick up and continue development with full context.

---

## 1. Project Overview

**ScanProof** is a Facility Operations Platform for cleaning, maintenance, and facility management. It uses QR-based scanning to track tasks, locations, issues, and generate automated reports.

---

## 2. Tech Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Backend | Laravel | 12.x |
| PHP | PHP | 8.2+ |
| Frontend | Livewire | 4.3+ |
| CSS | Tailwind CSS | 4.0+ (via `@tailwindcss/vite`) |
| Build | Vite | 7.0+ |
| Auth | Laravel Fortify | 1.37+ |
| Testing | PHPUnit | 11.5+ |
| Database (prod) | MySQL | - |
| Database (test) | SQLite | - |

---

## 3. What Has Been Completed

### 3.1 Landing Page
- Full landing page at `resources/views/pages/landing.blade.php`
- Blade components: `nav`, `hero`, `features` (6 features), `cta`, `footer`
- Route: `GET /` serves the landing page

### 3.2 Authentication System (via Fortify)
- Login page: `resources/views/auth/login.blade.php`
- Register page: `resources/views/auth/register.blade.php`
- Forgot password: `resources/views/auth/forgot-password.blade.php`
- Reset password: `resources/views/auth/reset-password.blade.php`
- Email verification: `resources/views/auth/verify-email.blade.php`
- Custom logout: `POST /logout` clears session, redirects to `/`
- Fortify configured in `config/fortify.php` and `app/Providers/FortifyServiceProvider.php`

### 3.3 User Roles & Permissions
- `app/Enums/UserRole.php` - Enum with 4 roles:
  - `Admin` -> `/admin/dashboard`
  - `Supervisor` -> `/supervisor/dashboard`
  - `Staff` -> `/staff/dashboard` (not implemented yet)
  - `Client` -> `/client/dashboard` (not implemented yet)
- `app/Http/Middleware/CheckRole.php` - Registered as `role` middleware in `bootstrap/app.php`
- Users table has `role` column (enum: admin, supervisor, staff, client; default: staff)

### 3.4 Admin Dashboard
- Layout: `resources/views/layouts/admin.blade.php` (292 lines - responsive sidebar, mobile bottom nav, top search bar)
- Page: `resources/views/pages/admin/dashboard.blade.php` (welcome banner, stats grid with placeholder "--", quick actions)
- Route: `GET /admin/dashboard` (requires `auth` + `role:admin` middleware)

### 3.5 Supervisor Dashboard
- Layout: `resources/views/layouts/supervisor.blade.php` (164 lines - sidebar, mobile nav)
- Page: `resources/views/pages/supervisor/dashboard.blade.php` (welcome banner, stats grid, quick actions)
- Route: `GET /supervisor/dashboard` (requires `auth` + `role:supervisor` middleware)

### 3.6 Auth Layout
- `resources/views/layouts/auth.blade.php` (112 lines - split-screen with left illustration, right form)

### 3.7 Supervisor Invitation Flow (BONUS - beyond Phase 1)
- `app/Models/SupervisorInvitation.php` - Model with token generation, expiry, status tracking
- `app/Http/Controllers/Admin/SupervisorInvitationController.php` - CRUD for invitations
- `app/Livewire/Admin/SupervisorManagement.php` - Full management UI (invite, cancel, demote)
- `app/Livewire/Supervisor/SetPassword.php` - Invited supervisor sets password via token link
- `app/Mail/SupervisorInvitationMail.php` - Branded invitation email
- `resources/views/emails/supervisor-invitation.blade.php` - Email template
- Routes: `GET /admin/supervisors`, `POST /admin/supervisors/invite`, `DELETE /admin/supervisors/{invitation}`, `GET /supervisor/set-password/{token}`

### 3.8 Database
- 7 migration files, 10+ tables:
  - `users` (with `role` column, 2FA columns)
  - `password_reset_tokens`
  - `sessions`
  - `cache`, `cache_locks`
  - `jobs`, `job_batches`, `failed_jobs`
  - `passkeys`
  - `supervisor_invitations`
- Database seeder: `admin@scanproof.com` / `password` (Admin role)
- SQLite used for testing, MySQL configured for production

### 3.9 Models
- `app/Models/User.php` - Has `isAdmin()`, `isSupervisor()`, `sentInvitations()` methods
- `app/Models/SupervisorInvitation.php` - Has `isExpired()`, `isAccepted()`, `isValidForUse()`, `markAccepted()`, `markExpired()`, `generateToken()`, scopes (`pending`, `expired`, `valid`)

### 3.10 Custom Theme
- `resources/css/app.css` - Custom Tailwind tokens: primary, success, warning, error, info, surface colors, text colors, sidebar colors
- Font: Inter (via Bunny Fonts CDN)

### 3.11 Tests
- **Feature Tests (8 files):**
  - `ExampleTest.php` - 1 test (passing)
  - `AuthRoutesTest.php` - 13 tests (all passing)
  - `AuthenticationTest.php` - 14 tests (partially passing)
  - `PasswordResetTest.php` - 7 tests (partially passing)
  - `SupervisorInvitationTest.php` - 9 tests
  - `FormSubmissionDebugTest.php` - 5 tests (debug)
  - `FormSubmissionFixedTest.php` - 3 tests (debug)
  - `FortifyActionHandlerDebugTest.php` - 4 tests (debug)
- **Unit Tests (1 file):**
  - `SupervisorInvitationTest.php` - 9 tests

### 3.12 Build & Assets
- Vite compiled assets in `public/build/`
- `composer setup` script for full project setup
- `composer dev` runs server, queue, pail, vite concurrently
- `composer test` clears config and runs tests

---

## 4. What Is NOT Done (Pending)

### 4.1 Missing Tests (from CONTEXT.md Phase 1 checklist)
- TC-1.1: Landing page display test
- TC-1.2: Landing page navigation test
- TC-1.6: Admin dashboard access control test
- TC-1.7: Admin dashboard content test
- TC-1.8: Admin layout test
- TC-1.9: Guest layout test
- TC-1.10: Database connection test

### 4.2 Known Issues
1. **`CreateNewUser` defaults to Admin role** - Fortify's `CreateNewUser` assigns `UserRole::Admin` to all new registrations (security concern)
2. **`RoleRedirectMiddleware` is defined but NOT registered** in `bootstrap/app.php`
3. **Form submission test failures** - POST to `/register`, `/login`, `/forgot-password` fail in tests (possible CSRF or Fortify integration issue)
4. **`dashboard.blade.php` (307 lines)** exists but is unused - contains hardcoded dummy data, not referenced by any route
5. **`welcome.blade.php`** is default Laravel page, unused by routes

### 4.3 Not Started (Phase 2+)

| Phase | Features |
|-------|----------|
| **Phase 2** | User CRUD, role assignment, permission middleware, profile management |
| **Phase 3** | Location CRUD, QR code generation, QR scan endpoint, location history |
| **Phase 4** | Task CRUD, task assignment, task status workflow, photo upload |
| **Phase 5** | Issue submission, public QR issue report, issue routing, issue tracking |
| **Phase 6** | Reports, PDF export, facility health score, dashboard analytics |
| **Phase 7** | Email notifications, WhatsApp integration, automated daily reports |
| **Phase 8** | GPS verification, SLA management, client portal, building map view |

---

## 5. File Structure

```
scanproof/
├── app/
│   ├── Actions/Fortify/          # CreateNewUser, ResetUserPassword, etc.
│   ├── Enums/UserRole.php        # Admin, Supervisor, Staff, Client
│   ├── Http/
│   │   ├── Controllers/Admin/SupervisorInvitationController.php
│   │   └── Middleware/
│   │       ├── CheckRole.php     # Registered as 'role'
│   │       ├── RoleRedirectMiddleware.php  # NOT registered
│   │       └── ValidateInvitationToken.php
│   ├── Livewire/
│   │   ├── Admin/SupervisorManagement.php
│   │   └── Supervisor/SetPassword.php
│   ├── Mail/SupervisorInvitationMail.php
│   ├── Models/
│   │   ├── User.php
│   │   └── SupervisorInvitation.php
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── FortifyServiceProvider.php
├── config/
│   ├── fortify.php
│   ├── livewire.php
│   └── (other Laravel defaults)
├── database/
│   ├── factories/UserFactory.php
│   ├── migrations/ (7 files)
│   └── seeders/DatabaseSeeder.php
├── resources/
│   ├── css/app.css               # Custom Tailwind theme tokens
│   ├── js/app.js, bootstrap.js
│   └── views/
│       ├── layouts/
│       │   ├── admin.blade.php   (292 lines)
│       │   ├── auth.blade.php    (112 lines)
│       │   └── supervisor.blade.php (164 lines)
│       ├── pages/
│       │   ├── landing.blade.php
│       │   ├── admin/dashboard.blade.php
│       │   └── supervisor/dashboard.blade.php
│       ├── components/ (5: nav, hero, features, cta, footer)
│       ├── auth/ (5: login, register, forgot-password, reset-password, verify-email)
│       ├── livewire/
│       │   ├── admin/supervisor-management.blade.php
│       │   └── supervisor/set-password.blade.php
│       ├── admin/supervisors/index.blade.php
│       └── emails/supervisor-invitation.blade.php
├── routes/web.php                 # 11 routes defined
├── tests/
│   ├── Feature/ (8 test files)
│   └── Unit/ (1 test file)
├── context.md                     # Original development plan
├── current_stage.md               # This file
├── composer.json, package.json, vite.config.js
└── public/build/ (compiled assets)
```

---

## 6. Routes Summary

| Method | URI | Middleware | Purpose |
|--------|-----|-----------|---------|
| GET | `/` | none | Landing page |
| GET | `/login` | guest | Fortify login |
| POST | `/login` | guest | Fortify login handler |
| GET | `/register` | guest | Fortify register |
| POST | `/register` | guest | Fortify register handler |
| GET | `/forgot-password` | guest | Fortify forgot password |
| POST | `/forgot-password` | guest | Fortify forgot password handler |
| GET | `/reset-password/{token}` | guest | Fortify reset password |
| POST | `/reset-password` | guest | Fortify reset handler |
| GET | `/email/verify` | auth | Email verification |
| POST | `/logout` | auth | Custom logout |
| GET | `/dashboard` | auth | Role-based redirect |
| GET | `/admin` | auth, role:admin | Redirect to admin/dashboard |
| GET | `/admin/dashboard` | auth, role:admin | Admin dashboard |
| GET | `/admin/supervisors` | auth, role:admin | Supervisor management |
| POST | `/admin/supervisors/invite` | auth, role:admin | Create invitation |
| DELETE | `/admin/supervisors/{invitation}` | auth, role:admin | Cancel invitation |
| GET | `/supervisor/dashboard` | auth, role:supervisor | Supervisor dashboard |
| GET | `/supervisor/set-password/{token}` | ValidateInvitationToken | Set password |

---

## 7. Database Schema

### users
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| name | varchar | |
| email | varchar | unique |
| role | varchar | enum: admin/supervisor/staff/client, default: staff |
| email_verified_at | timestamp | nullable |
| password | varchar | hashed |
| two_factor_secret | text | nullable |
| two_factor_recovery_codes | text | nullable |
| two_factor_confirmed_at | timestamp | nullable |
| remember_token | varchar | nullable |
| created_at, updated_at | timestamps | |

### supervisor_invitations
| Column | Type | Notes |
|--------|------|-------|
| id | bigint PK | auto-increment |
| email | varchar | |
| token | varchar(64) | unique |
| invited_by | bigint FK | references users.id |
| expires_at | timestamp | |
| accepted_at | timestamp | nullable |
| status | varchar | enum: pending/accepted/expired |
| created_at, updated_at | timestamps | |

---

## 8. Environment & Config Notes

- `.env` exists with MySQL configured for `scanproof` database
- PHPUnit uses SQLite in-memory, array mail, sync queue, array session
- Vite build is working (compiled assets exist in `public/build/`)
- Custom composer scripts: `setup`, `dev`, `test`

---

## 9. Instructions for Claude Fable 5

When continuing development:

1. **Read `context.md`** for the full development plan and all 8 phases
2. **Read `current_stage.md`** (this file) for exact current state
3. **Phase 1 is mostly done** - only missing some test cases (TC-1.1, TC-1.2, TC-1.6, TC-1.7, TC-1.8, TC-1.9, TC-1.10)
4. **Fix known issues first** before moving to Phase 2:
   - Fix `CreateNewUser` to not default to Admin role
   - Register `RoleRedirectMiddleware` or remove it
   - Fix form submission test failures
5. **Then proceed to Phase 2** (User Management)
6. **Development rules:**
   - No file exceeds 300 lines
   - Modular folder structure by feature/domain
   - Phase-based: no Phase 2 until Phase 1 passes all tests
   - Test-driven: each feature needs passing tests

---

## 10. Quick Commands

```bash
# Setup
composer setup

# Run dev server
composer dev

# Run tests
composer test

# Run specific test
php artisan test --filter=AuthRoutesTest

# Build assets
npm run build

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed
```
