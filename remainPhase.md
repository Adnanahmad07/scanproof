# ScanProof — Issue Resolve Flow Tracker

> Last updated: 2026-08-15
> Rule: One phase at a time. All tests green before moving to next phase.

---

## Current Flow

```
Anonymous scans QR → fills form → POST /r/{uuid}/report
        ↓
Issue created (SP-XXXXXX tracking code, status: reported)
        ↓
IssueReportedNotification queued → supervisor email
        ↓
Supervisor visits /supervisor/issues → sees issue
        ↓
Supervisor clicks "Assign" → selects worker → status = assigned
        ↓
Worker visits /staff/issues → sees assigned issue
        ↓
Worker clicks "Start" → status = in_progress
        ↓
Worker clicks "Complete" → status = resolved
        ↓
Public tracking page /track/SP-XXXXXX updates timeline
```

---

## Phase 1: Fix Staff Issues Page Layout

**Status:** `completed` ✅
**Goal:** Staff issues page renders with proper sidebar, nav, and styling (same bug that existed on supervisor issues page)

### Problem
`StaffIssueController@index` returns `view('livewire.staff.issue-list', compact('issues'))` directly — a raw Blade partial with no layout, no sidebar, no CSS.

### Changes
1. Create `resources/views/pages/staff/issues.blade.php` — wrapper extending `layouts.staff`
2. Update `StaffIssueController@index` to return `view('pages.staff.issues', compact('issues'))`
3. Verify styling matches other staff pages (dashboard, tasks, scan)

### Test Cases — TC-P1.x

| ID | Test | Pass Criteria |
|---|---|---|
| TC-P1.1 | Staff can access issues page via sidebar | GET /staff/issues returns 200, contains sidebar nav |
| TC-P1.2 | Unauthenticated user cannot access staff issues | GET /staff/issues redirects to /login |
| TC-P1.3 | Non-staff user cannot access staff issues | Supervisor/Admin get 403 or redirect |
| TC-P1.4 | Issues page extends staff layout | Response HTML contains `layouts.staff` sidebar elements |
| TC-P1.5 | Issues page shows assigned issues | Response contains issue description/tracking code |
| TC-P1.6 | Staff issues page renders with Vite CSS | Response contains compiled CSS asset reference |
| TC-P1.7 | Staff issue show page works with layout | GET /staff/issues/{id} returns 200 for assigned issue |
| TC-P1.8 | Staff issue show page blocks other staff issues | GET /staff/issues/{other_id} returns 403 |

---

## Phase 2: Notifications That Complete the Loop

**Status:** `completed` ✅
**Goal:** Worker gets notified when assigned. Supervisor gets notified when issue resolved.

### Problem
- No notification to staff when supervisor assigns an issue — worker doesn't know they have work
- No notification to supervisor when staff resolves — supervisor doesn't know it's done

### Changes
1. Create `app/Notifications/IssueAssignedNotification.php` — queued mail to staff
2. Create `app/Notifications/IssueResolvedNotification.php` — queued mail to supervisor
3. Wire `IssueAssignedNotification` into `SupervisorIssueController@assign`
4. Wire `IssueResolvedNotification` into `StaffIssueController@updateStatus` (when status = resolved)
5. Both notifications implement `ShouldQueue` (non-blocking)

### Test Cases — TC-P2.x

| ID | Test | Pass Criteria |
|---|---|---|
| TC-P2.1 | Staff receives notification on issue assignment | Notification::fake(), assertIssued(IssueAssignedNotification) |
| TC-P2.2 | Supervisor receives notification on issue resolution | Notification::fake(), assertIssued(IssueResolvedNotification) |
| TC-P2.3 | Notification not sent to self (assigner doesn't get assign email) | assertNotSentTo() |
| TC-P2.4 | Notification contains tracking code | Notification content contains SP-XXXXXX |
| TC-P2.5 | Notification contains location name | Notification content contains location name |
| TC-P2.6 | Notifications are queued (not synchronous) | ShouldQueue interface implemented |

---

## Phase 3: Supervisor Convert-to-Task Form

**Status:** `completed` ✅
**Goal:** Supervisor can customize category, priority, and due date when converting issue to task

### Problem
`SupervisorIssueController@convertToTask` creates Task with hardcoded defaults: category=other, priority=medium, due_date=null. No form to customize.

### Changes
1. Add modal form in `livewire/supervisor/issue-dashboard.blade.php` with:
   - Category dropdown (cleaning/maintenance/inspection/other)
   - Priority dropdown (low/medium/high/urgent)
   - Due date picker (optional)
2. Update `SupervisorIssueController@convertToTask` to accept `category`, `priority`, `due_date` fields
3. Validate inputs (same rules as TaskAssignment Livewire component)

### Test Cases — TC-P3.x

| ID | Test | Pass Criteria |
|---|---|---|
| TC-P3.1 | Convert to task with custom category | Task created with selected category |
| TC-P3.2 | Convert to task with custom priority | Task created with selected priority |
| TC-P3.3 | Convert to task with due date | Task created with correct due_date |
| TC-P3.4 | Convert to task without due date | Task created with null due_date |
| TC-P3.5 | Invalid category rejected | 422 validation error |
| TC-P3.6 | Invalid priority rejected | 422 validation error |
| TC-P3.7 | Past due date rejected | 422 validation error |
| TC-P3.8 | Task links to issue via issue_id | Task.issue_id matches issue.id |

---

## Phase 4: Admin Dashboard Real Stats

**Status:** `pending`
**Goal:** Admin dashboard shows real counts instead of "--" placeholders

### Problem
`pages/admin/dashboard.blade.php` has hardcoded "--" for Total Staff, Active Tasks, Locations, Open Issues.

### Changes
1. Update admin dashboard to query real counts:
   - Total Users (by role)
   - Total Tasks (pending/in_progress/completed)
   - Total Locations (configured)
   - Open Issues (reported/assigned/in_progress)
2. Replace "--" with actual numbers
3. Make stat cards clickable to relevant pages

### Test Cases — TC-P4.x

| ID | Test | Pass Criteria |
|---|---|---|
| TC-P4.1 | Admin dashboard shows real total users | Count matches DB users table |
| TC-P4.2 | Admin dashboard shows real task counts | Count matches DB tasks table |
| TC-P4.3 | Admin dashboard shows real location count | Count matches DB locations table |
| TC-P4.4 | Admin dashboard shows real open issue count | Count matches DB issues table (non-resolved) |
| TC-P4.5 | Stats update after new issue created | Create issue, refresh, count increases |

---

## Phase 5: Integration Tests (Full Flow)

**Status:** `pending`
**Goal:** End-to-end test proving the complete resolve flow works

### Test Cases — TC-P5.x

| ID | Test | Pass Criteria |
|---|---|---|
| TC-P5.1 | Full flow: report → assign → start → resolve | Issue status progresses through all states |
| TC-P5.2 | Full flow with notifications | Both IssueAssigned and IssueResolved notifications sent |
| TC-P5.3 | Full flow with custom task conversion | Task created with supervisor-selected fields |
| TC-P5.4 | Full flow with public tracking | Tracking page shows all status transitions |
| TC-P5.5 | Full flow with photo attachment | Issue created with photo, visible in supervisor view |
| TC-P5.6 | Anonymous reporter gets tracking code | Response redirects to /track/SP-XXXXXX |
| TC-P5.7 | Staff cannot resolve other staff's issues | 403 on unauthorized status change |
| TC-P5.8 | Supervisor cannot assign to non-existent user | 422 validation error |

---

## Completed Phases

| Phase | Completed | Tests Passed |
|---|---|---|
| Phase 1 | 2026-08-15 | 11/11 |
| Phase 2 | 2026-08-15 | 6/6 |
| Phase 3 | 2026-08-15 | 8/8 |
| Phase 4 | - | - |
| Phase 5 | - | - |
