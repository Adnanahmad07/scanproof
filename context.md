# ScanProof - Project Context & Development Plan

## Project Overview

**ScanProof** is a Facility Operations Platform for cleaning, maintenance, and facility management. It uses QR-based scanning to track tasks, locations, issues, and generate automated reports.

**Client:** Bader Almalki

**Tech Stack:**
- Backend: Laravel 12
- Frontend: Livewire 4 + Tailwind CSS 4
- Database: MySQL
- Build: Vite
- PHP: 8.2+

---

## Development Rules

1. **No file exceeds 300 lines of code** - Split into smaller files/modules
2. **Modular folder structure** - Organized by feature/domain
3. **Phase-based development** - No Phase 2 until Phase 1 completes and passes all tests
4. **Test-driven** - Each feature must have test cases that pass before moving forward

---

## Project Structure

```
scanproof/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   └── Livewire/
│   ├── Models/
│   ├── Services/
│   ├── Enums/
│   ├── Observers/
│   └── Policies/
├── resources/
│   ├── views/
│   │   ├── layouts/
│   │   ├── livewire/
│   │   ├── components/
│   │   └── pages/
│   └── css/
├── routes/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── tests/
│   ├── Feature/
│   └── Unit/
└── context.md
```

---

## User Roles & Permissions

| Role | Access Level |
|------|-------------|
| System Admin | Full access to all features |
| Supervisor | Task management, reports, staff oversight |
| Cleaning Staff | Task view/update, QR scan, issue report |
| Maintenance Technician | Task view/update, QR scan, issue report |
| Client / Read-Only | View reports, facility health, issue report |

---

## Development Phases

### PHASE 1 - Foundation (Current)

**Scope:** Landing Page + Admin Home Dashboard

| # | Feature | Status | Tests |
|---|---------|--------|-------|
| 1.1 | Landing page with hero, features, CTA | ✅ Complete | See below |
| 1.2 | Admin login/logout | Pending | See below |
| 1.3 | Admin dashboard home | Pending | See below |
| 1.4 | Basic layout (admin, guest) | Pending | See below |
| 1.5 | Database setup (users table) | Pending | See below |

### PHASE 2 - User Management (After Phase 1)

| # | Feature | Status |
|---|---------|--------|
| 2.1 | User CRUD (Admin) |
| 2.2 | Role assignment |
| 2.3 | Permission middleware |
| 2.4 | Profile management |

### PHASE 3 - Location & QR (After Phase 2)

| # | Feature | Status |
|---|---------|--------|
| 3.1 | Location CRUD |
| 3.2 | QR code generation |
| 3.3 | QR scan endpoint |
| 3.4 | Location history |

### PHASE 4 - Task Management (After Phase 3)

| # | Feature | Status |
|---|---------|--------|
| 4.1 | Task CRUD |
| 4.2 | Task assignment |
| 4.3 | Task status workflow |
| 4.4 | Photo upload |

### PHASE 5 - Issue Reporting (After Phase 4)

| # | Feature | Status |
|---|---------|--------|
| 5.1 | Issue submission |
| 5.2 | Public QR issue report |
| 5.3 | Issue routing |
| 5.4 | Issue tracking |

### PHASE 6 - Reports & Analytics (After Phase 5)

| # | Feature | Status |
|---|---------|--------|
| 6.1 | Daily/Weekly/Monthly reports |
| 6.2 | PDF export |
| 6.3 | Facility Health Score |
| 6.4 | Dashboard analytics |

### PHASE 7 - Automated Communication (After Phase 6)

| # | Feature | Status |
|---|---------|--------|
| 7.1 | Email notifications |
| 7.2 | WhatsApp integration |
| 7.3 | Automated daily reports |
| 7.4 | Smart notifications |

### PHASE 8 - Advanced Features (After Phase 7)

| # | Feature | Status |
|---|---------|--------|
| 8.1 | GPS verification |
| 8.2 | SLA management |
| 8.3 | Client portal |
| 8.4 | Building map view |

---

## Phase 1 - Test Cases

### TC-1.1: Landing Page Display

```
TEST CASE ID: TC-1.1
TEST CASE NAME: Landing page renders correctly
PRECONDITION: User is not logged in

STEPS:
1. Navigate to /
2. Verify HTTP status is 200
3. Verify page contains "ScanProof" branding
4. Verify hero section is visible
5. Verify features section is visible
6. Verify CTA button is visible
7. Verify navigation links are present

EXPECTED RESULT: Landing page displays with all sections
PASS/FAIL: ____
```

### TC-1.2: Landing Page Navigation

```
TEST CASE ID: TC-1.2
TEST CASE NAME: Landing page navigation works
PRECONDITION: User is on landing page

STEPS:
1. Click "Login" link
2. Verify redirect to /login
3. Navigate back to /
4. Click "Features" anchor
5. Verify scroll to features section

EXPECTED RESULT: All navigation links function correctly
PASS/FAIL: ____
```

### TC-1.3: Admin Login - Success

```
TEST CASE ID: TC-1.3
TEST CASE NAME: Admin can login successfully
PRECONDITION: Admin user exists in database

STEPS:
1. Navigate to /login
2. Enter valid admin email
3. Enter valid password
4. Click "Login" button
5. Verify redirect to /admin/dashboard
6. Verify session contains user data

EXPECTED RESULT: Admin logged in and redirected to dashboard
PASS/FAIL: ____
```

### TC-1.4: Admin Login - Failure

```
TEST CASE ID: TC-1.4
TEST CASE NAME: Invalid credentials rejected
PRECONDITION: Login page is accessible

STEPS:
1. Navigate to /login
2. Enter invalid email
3. Enter invalid password
4. Click "Login" button
5. Verify stay on /login
6. Verify error message displayed

EXPECTED RESULT: Login fails with appropriate error
PASS/FAIL: ____
```

### TC-1.5: Admin Logout

```
TEST CASE ID: TC-1.5
TEST CASE NAME: Admin can logout
PRECONDITION: Admin is logged in

STEPS:
1. Navigate to /admin/dashboard
2. Click logout button
3. Verify session is cleared
4. Verify redirect to /
5. Try accessing /admin/dashboard
6. Verify redirect to /login

EXPECTED RESULT: User logged out and protected routes inaccessible
PASS/FAIL: ____
```

### TC-1.6: Admin Dashboard - Access Control

```
TEST CASE ID: TC-1.6
TEST CASE NAME: Unauthenticated user cannot access admin dashboard
PRECONDITION: User is not logged in

STEPS:
1. Navigate to /admin/dashboard
2. Verify redirect to /login
3. Navigate to /admin
4. Verify redirect to /login

EXPECTED RESULT: Admin routes protected by auth middleware
PASS/FAIL: ____
```

### TC-1.7: Admin Dashboard - Content

```
TEST CASE ID: TC-1.7
TEST CASE NAME: Admin dashboard displays required elements
PRECONDITION: Admin is logged in

STEPS:
1. Login as admin
2. Navigate to /admin/dashboard
3. Verify sidebar navigation is present
4. Verify user info is displayed
5. Verify stats cards are present (placeholder)
6. Verify page title shows "Dashboard"

EXPECTED RESULT: Dashboard displays all required UI elements
PASS/FAIL: ____
```

### TC-1.8: Admin Layout

```
TEST CASE ID: TC-1.8
TEST CASE NAME: Admin layout renders correctly
PRECONDITION: Admin is logged in

STEPS:
1. Login as admin
2. Navigate to /admin/dashboard
3. Verify sidebar is visible
4. Verify top navigation is visible
5. Verify main content area exists
6. Verify footer exists

EXPECTED RESULT: Admin layout with sidebar, nav, content, footer
PASS/FAIL: ____
```

### TC-1.9: Guest Layout

```
TEST CASE ID: TC-1.9
TEST CASE NAME: Guest layout renders correctly
PRECONDITION: User is not logged in

STEPS:
1. Navigate to /
2. Verify header navigation is visible
3. Verify content area is full-width
4. Verify footer is visible
5. Verify no sidebar present

EXPECTED RESULT: Guest layout without sidebar
PASS/FAIL: ____
```

### TC-1.10: Database Connection

```
TEST CASE ID: TC-1.10
TEST CASE NAME: Database connection works
PRECONDITION: MySQL is running, .env configured

STEPS:
1. Run php artisan migrate
2. Verify users table created
3. Verify sessions table created
4. Verify cache table created
5. Run php artisan tinker
6. Create test user: User::create([...])
7. Verify user exists in database

EXPECTED RESULT: Database tables created and CRUD works
PASS/FAIL: ____
```

---

## Phase 1 - Implementation Checklist

### 1.1 Landing Page ✅
- [x] Create landing page view `resources/views/pages/landing.blade.php`
- [x] Add hero section
- [x] Add features section (6 features)
- [x] Add CTA section
- [x] Add footer
- [x] Route: `GET /` → landing page
- [ ] Write test TC-1.1, TC-1.2

### 1.2 Auth System
- [ ] Install Livewire if needed
- [ ] Create login Livewire component
- [ ] Create login view
- [ ] Add auth routes
- [ ] Add auth middleware
- [ ] Write test TC-1.3, TC-1.4, TC-1.5

### 1.3 Admin Dashboard
- [ ] Create admin layout component
- [ ] Create admin dashboard Livewire component
- [ ] Create admin dashboard view
- [ ] Add admin routes with middleware
- [ ] Write test TC-1.6, TC-1.7, TC-1.8

### 1.4 Guest Layout
- [ ] Create guest layout component
- [ ] Update landing page to use guest layout
- [ ] Write test TC-1.9

### 1.5 Database
- [ ] Verify migrations run
- [ ] Create user seeder for admin
- [ ] Write test TC-1.10

---

## Sign-Off

Phase 1 is complete when:
1. All 10 test cases pass
2. No file exceeds 300 lines
3. Code follows Laravel conventions
4. All features are functional (not just UI)

**Phase 1 Approval:** _________________ Date: __________
