# ScanProof - QA Test Plan (Phases 1-5)
**Date:** 2026-08-16
**QA Engineer:** AI Senior QA

---

## Phase 1: Notification System
| Test ID | Description | Type | Edge Cases |
|---------|-------------|------|------------|
| P1-TC01 | Notification dropdown renders for authenticated user | Feature | Unauthenticated access |
| P1-TC02 | Unread count shows correct number | Feature | 0, 1, 99, 100+ |
| P1-TC03 | Mark notification as read | Feature | Invalid ID, already read |
| P1-TC04 | Mark all as read | Feature | No unread, concurrent |
| P1-TC05 | Notification dispatches on issue reported | Feature | No supervisor assigned |
| P1-TC06 | Notification dispatches on task assigned | Feature | Unassigned task |
| P1-TC07 | Notification dispatches on task completed | Feature | No supervisor |
| P1-TC08 | Dropdown toggle open/close | Feature | Click outside |
| P1-TC09 | Notification types display correct icons | Unit | Unknown type |
| P1-TC10 | Notifications stored in database | Unit | Data integrity |

## Phase 2: Global Search
| Test ID | Description | Type | Edge Cases |
|---------|-------------|------|------------|
| P2-TC01 | Search with < 2 chars returns nothing | Feature | Empty, 1 char |
| P2-TC02 | Search finds tasks by title | Feature | Special chars, SQL injection |
| P2-TC03 | Search finds issues by description | Feature | Long text, unicode |
| P2-TC04 | Search finds issues by tracking code | Feature | Partial match |
| P2-TC05 | Search finds locations by name | Feature | Duplicate names |
| P2-TC06 | Search finds users (admin only) | Feature | Non-admin role |
| P2-TC07 | Search results limited to 5 per type | Unit | 10+ results |
| P2-TC08 | Search is role-scoped | Feature | Staff sees limited |
| P2-TC09 | Close search clears query | Feature | Rapid open/close |
| P2-TC10 | Debounce prevents rapid API calls | Unit | 10 rapid keystrokes |

## Phase 3: Recurring Tasks
| Test ID | Description | Type | Edge Cases |
|---------|-------------|------|------------|
| P3-TC01 | RecurringTask model creates successfully | Unit | All fields |
| P3-TC02 | shouldGenerateToday daily always true | Unit | Already generated today |
| P3-TC03 | shouldGenerateToday weekly checks day | Unit | Wrong day |
| P3-TC04 | shouldGenerateToday monthly checks day | Unit | Day 31 in 30-day month |
| P3-TC05 | shouldGenerateToday inactive returns false | Unit | Paused task |
| P3-TC06 | generateTask creates Task + updates last_generated_at | Unit | Null timestamps |
| P3-TC07 | Artisan command generates daily tasks | Feature | No tasks due |
| P3-TC08 | Supervisor can create recurring task via Livewire | Feature | Missing required fields |
| P3-TC09 | Supervisor can edit recurring task | Feature | Non-existent task |
| P3-TC10 | Supervisor can deactivate recurring task | Feature | Already inactive |
| P3-TC11 | Non-supervisor cannot access recurring tasks | Feature | Staff, admin |
| P3-TC12 | Day of week/month validation | Unit | Out of range |

## Phase 4: Admin Enhancements
| Test ID | Description | Type | Edge Cases |
|---------|-------------|------|------------|
| P4-TC01 | Admin can promote staff to supervisor | Feature | Already supervisor |
| P4-TC02 | Admin can demote supervisor to staff | Feature | Not supervisor |
| P4-TC03 | Admin cannot change own role | Feature | Self-promotion |
| P4-TC04 | Non-admin cannot promote | Feature | Supervisor, staff |
| P4-TC05 | Promoted user has supervisor role | Unit | Role enum |
| P4-TC06 | Demoted user has staff role | Unit | Role enum |
| P4-TC07 | AnalyticsService returns correct stats | Unit | Empty DB |
| P4-TC08 | HealthScoreService calculates correctly | Unit | No tasks/issues |
| P4-TC09 | DatabaseSeeder creates realistic data | Feature | Idempotency |
| P4-TC10 | User management page loads for admin | Feature | Non-admin |

## Phase 5: Photo Upload + Quality Control
| Test ID | Description | Type | Edge Cases |
|---------|-------------|------|------------|
| P5-TC01 | TaskPhotoUpload component renders | Feature | No task |
| P5-TC02 | Photo validation: valid mime types | Unit | gif, bmp, svg |
| P5-TC03 | Photo validation: max 5MB | Unit | 6MB file |
| P5-TC04 | Photo stored in correct directory | Unit | Path structure |
| P5-TC05 | Before photos shown in task list | Feature | No photos |
| P5-TC06 | After photos shown in task list | Feature | No photos |
| P5-TC07 | Supervisor can view quality control page | Feature | Wrong supervisor |
| P5-TC08 | Quality control shows before/after comparison | Feature | Missing photos |
| P5-TC09 | Staff can delete own photos | Feature | Other's photos |
| P5-TC10 | Task detail page loads for assigned staff | Feature | Wrong staff |
| P5-TC11 | Task detail shows photo upload component | Feature | Completed task |
| P5-TC12 | Photo thumbnails display in task lists | Feature | Many photos |

---

## Test Execution Order
1. Unit tests first (models, services)
2. Feature tests (Livewire components, routes)
3. Integration tests (full flows)
4. Edge case tests (validation, auth, errors)

## Pass Criteria
- All tests pass
- No regression in existing tests
- Code coverage > 80% for new features
