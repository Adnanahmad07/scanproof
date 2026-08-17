# ScanProof - Feature Implementation Plan
**Date:** 2026-08-16

---

## Overview
Implementing 5 major features using full Laravel ecosystem (Livewire, Notifications, Queue, Storage, etc.)

---

## Phase 1: Notification System
**Goal:** Real-time notification popup in sidebar + red badge for new issues

### What to build:
- [ ] Notification dropdown component (Livewire) in supervisor & admin sidebar
- [ ] Red badge counter on bell icon (unread count)
- [ ] Mark as read / mark all as read
- [ ] Auto-trigger notifications when:
  - New issue reported → notify supervisor
  - Task assigned → notify staff
  - Task completed → notify supervisor
- [ ] `wire:poll` for live updates (no websocket needed)

### Files:
- `app/Livewire/NotificationDropdown.php` (new)
- `resources/views/livewire/notification-dropdown.blade.php` (new)
- Add to `layouts/supervisor.blade.php`, `layouts/admin.blade.php`, `layouts/staff.blade.php`
- Dispatch notifications in `IssueReportController`, `TaskAssignment`, `Staff\TaskList`

---

## Phase 2: Search Functionality
**Goal:** Global search across tasks, issues, locations, users

### What to build:
- [ ] Global search bar (already exists as placeholder in admin layout)
- [ ] Livewire search component with debounced input
- [ ] Search results dropdown with categories (Tasks, Issues, Locations, Users)
- [ ] Click result → navigate to detail page
- [ ] Search scopes on models (Task, Issue, Location, User)

### Files:
- `app/Livewire/GlobalSearch.php` (new)
- `resources/views/livewire/global-search.blade.php` (new)
- Add search scopes to models

---

## Phase 3: Task Section - Recurring Daily Tasks
**Goal:** Make tasks fully workable + add recurring/daily task support

### What to build:
- [ ] Recurring task model (daily, weekly, monthly patterns)
- [ ] `recurring_tasks` table migration
- [ ] Auto-generate task instances from recurring templates
- [ ] Artisan command to generate today's recurring tasks
- [ ] Supervisor UI: create recurring task template (e.g., "Floor Cleaning" daily)
- [ ] Staff UI: see today's recurring tasks alongside regular tasks
- [ ] Fix task status transitions (already have model logic, wire up UI properly)
- [ ] Task detail view with full workflow

### Files:
- `app/Models/RecurringTask.php` (new)
- `database/migrations/xxxx_create_recurring_tasks_table.php` (new)
- `app/Console/Commands/GenerateRecurringTasks.php` (new)
- `app/Livewire/Supervisor/TaskManagement.php` (enhanced)
- `resources/views/livewire/supervisor/task-management.blade.php` (new)

---

## Phase 4: Admin - Worker as Supervisor + Fix Analytics
**Goal:** Admin can promote workers to supervisor, fix analytics dashboard

### What to build:
- [ ] "Promote to Supervisor" action in admin user management
- [ ] Auto-assign supervisor_id when promoting
- [ ] Fix analytics service to use real data (currently returns 0s)
- [ ] Fix admin dashboard charts/visualizations
- [ ] Add supervisor_id to user creation flow (admin can assign supervisor)
- [ ] Seed realistic data for analytics demo

### Files:
- `app/Livewire/Admin/UserManagement.php` (add promote action)
- `app/Services/AnalyticsService.php` (fix queries)
- `resources/views/livewire/admin/user-management.blade.php` (add promote button)

---

## Phase 5: Before/After Photo Upload
**Goal:** Staff uploads before/after photos, supervisor views for quality control

### What to build:
- [ ] Photo upload Livewire component (drag & drop or file select)
- [ ] Store photos in `storage/app/public/task-photos/{task_id}/`
- [ ] Before photo required when starting task (in_progress)
- [ ] After photo required when completing task
- [ ] Photo validation (jpeg/png/webp, max 5MB)
- [ ] Supervisor task detail view showing before/after comparison
- [ ] Staff dashboard: show photos per task
- [ ] Quality control: supervisor can view photo gallery per task

### Files:
- `app/Livewire/Staff/TaskPhotoUpload.php` (new)
- `resources/views/livewire/staff/task-photo-upload.blade.php` (new)
- `app/Livewire/Supervisor/TaskDetail.php` (new)
- `resources/views/livewire/supervisor/task-detail.blade.php` (new)
- Enhance `app/Livewire/Staff/TaskList.php`

---

## Implementation Order
1. Phase 1 (Notifications) - Foundation for all other features
2. Phase 2 (Search) - Quick win, improves UX
3. Phase 3 (Recurring Tasks) - Core business logic
4. Phase 4 (Admin fixes) - Quick fixes
5. Phase 5 (Photos) - Quality control feature

---

## Tech Decisions
- **Livewire 3** for all interactive components (already in use)
- **wire:poll** for notification updates (no websockets)
- **Laravel Notifications** (database channel) for in-app notifications
- **Laravel Storage** for photo uploads
- **Alpine.js** for UI interactions (already in use via Vite)
- **Tailwind CSS** for styling (already in use)

---

## Status
- [ ] Phase 1: NOT STARTED
- [ ] Phase 2: NOT STARTED
- [ ] Phase 3: NOT STARTED
- [ ] Phase 4: NOT STARTED
- [ ] Phase 5: NOT STARTED
