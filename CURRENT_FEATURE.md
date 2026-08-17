# ScanProof — Simplified QR + Issue Reporting Flow

> **Status:** Design — Ready to implement
> **Last updated:** 2026-06-27
> **Priority:** HIGH — Core product flow

---

## 1. The Simple Flow (How It Actually Works)

```
┌─────────────────────────────────────────────────────────────────┐
│  STEP 1: Admin creates a building & generates blank QR codes   │
│          Admin prints QR PDF sheets                             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 2: Supervisor takes printed QR stickers                   │
│          Physically sticks them on doors, walls, assets         │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 3: Supervisor scans QR for the FIRST TIME                │
│          Dialog pops up:                                        │
│          ┌──────────────────────────────┐                       │
│          │  Room Number: [  101  ]       │                       │
│          │  Type: [ Lobby      ▼]        │                       │
│          │  [ Save ]                     │                       │
│          └──────────────────────────────┘                       │
│          QR is now "configured" with room info                  │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 4: Anyone scans the QR (anonymous/visitor)               │
│          Sees:                                                   │
│          ┌──────────────────────────────┐                       │
│          │  📍 Room 101                  │                       │
│          │  Type: Lobby                  │                       │
│          │                              │                       │
│          │  [ 🛠 Report Issue ]          │                       │
│          └──────────────────────────────┘                       │
│          Clicks "Report Issue" → fills simple form              │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 5: Issue appears on Supervisor Dashboard                 │
│          "Issue from Room 101 (Lobby)"                          │
│          Supervisor assigns a worker                            │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────────┐
│  STEP 6: Worker scans the QR                                    │
│          Sees the assigned issue for that room                  │
│          Updates status / marks complete                         │
└─────────────────────────────────────────────────────────────────┘
```

---

## 2. What Changes (Current → New)

### What we REMOVE (complex parts):
- Building > Floor > Room hierarchy (too complex for this use case)
- Supervisor creating child locations manually
- Admin assigning supervisors to locations
- Complex location management UI
- Supervisor QR Management page with filters/grouping

### What we SIMPLIFY:
| Current | New |
|---|---|
| Admin creates full location tree | Admin creates building + prints blank QRs |
| Supervisor browses locations | Supervisor just scans QR to configure it |
| Complex location CRUD | Simple: scan → enter room# + type → done |
| Supervisor has QR management page | Supervisor has issues dashboard |

### What we ADD:
- `/r/{uuid}` scan endpoint (currently missing)
- "First scan" configuration dialog for supervisor
- Public issue reporting form (anonymous)
- Issue model + migration
- Supervisor issue dashboard
- Worker issue view

---

## 3. Database Changes

### 3.1 Simplify `locations` table

The existing `locations` table is fine but we stop using the hierarchy. A location is simply:

| Column | Type | Purpose |
|---|---|---|
| `id` | bigint | Primary key |
| `uuid` | uuid | QR code identifier (never exposed as number) |
| `name` | string | Room number, e.g. "101", "Lobby A" |
| `type` | string | lobby, office, kitchen, bathroom, corridor, storage, other |
| `building` | string | Building name (set by admin when generating QRs) |
| `notes` | nullable text | Optional description |
| `configured` | boolean | `false` = blank QR, `true` = supervisor filled in info |
| `configured_by` | nullable FK | Who configured it (supervisor) |
| `configured_at` | nullable timestamp | When it was configured |
| `created_by` | nullable FK | Admin who generated the QR |
| `supervisor_id` | nullable FK | Building's assigned supervisor |
| `uuid` | timestamps | Created/updated |

**Migration:** Add `configured`, `configured_by`, `configured_at` columns. Remove `parent_id`, `floor` (unused in new flow).

### 3.2 Create `issues` table

```php
Schema::create('issues', function (Blueprint $table) {
    $table->id();
    $table->string('tracking_code')->unique(); // SP-XXXXXX
    $table->foreignId('location_id')->constrained()->nullOnDelete();
    $table->text('description');
    $table->string('photo_path')->nullable(); // optional photo
    $table->enum('status', ['reported', 'assigned', 'in_progress', 'resolved'])->default('reported');
    $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
    $table->foreignId('reported_by')->nullable()->constrained('users')->nullOnDelete(); // null = anonymous
    $table->timestamp('resolved_at')->nullable();
    $table->timestamps();
});
```

### 3.3 Create `issue_events` table (audit trail)

```php
Schema::create('issue_events', function (Blueprint $table) {
    $table->id();
    $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
    $table->string('from_status')->nullable();
    $table->string('to_status');
    $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
    $table->text('note')->nullable();
    $table->timestamps();
});
```

---

## 4. Models

### 4.1 Update `Location` model

Remove: `parent()`, `children()`, `scopeRoots()`, hierarchy logic.
Add: `configured`, `configuredBy()`, `scopeConfigured()`, `scopeBlank()`, `track()`.

```php
// Key additions:
protected $fillable = [
    'uuid', 'name', 'type', 'building', 'notes',
    'configured', 'configured_by', 'configured_at',
    'created_by', 'supervisor_id',
];

protected function casts(): array
{
    return [
        'type' => LocationType::class,
        'configured' => 'boolean',
        'configured_at' => 'datetime',
    ];
}

public function configuredBy(): BelongsTo
{
    return $this->belongsTo(User::class, 'configured_by');
}

public function scopeConfigured(Builder $query): Builder
{
    return $query->where('configured', true);
}

public function scopeBlank(Builder $query): Builder
{
    return $query->where('configured', false);
}

public function issues(): HasMany
{
    return $this->hasMany(Issue::class);
}
```

### 4.2 Create `Issue` model

```php
class Issue extends Model
{
    protected $fillable = [
        'tracking_code', 'location_id', 'description', 'photo_path',
        'status', 'assigned_to', 'reported_by', 'resolved_at',
    ];

    protected function casts(): array
    {
        return [
            'resolved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Issue $issue) {
            if (empty($issue->tracking_code)) {
                $issue->tracking_code = 'SP-' . strtoupper(Str::random(6));
            }
        });
    }

    public function location(): BelongsTo { return $this->belongsTo(Location::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to'); }
    public function reporter(): BelongsTo { return $this->belongsTo(User::class, 'reported_by'); }
    public function events(): HasMany { return $this->hasMany(IssueEvent::class); }

    public function scopeForSupervisor(Builder $query, int $userId): Builder
    {
        return $query->whereHas('location', fn ($q) => $q->where('supervisor_id', $userId));
    }
}
```

### 4.3 Create `IssueEvent` model

```php
class IssueEvent extends Model
{
    protected $fillable = ['issue_id', 'from_status', 'to_status', 'actor_id', 'note'];

    public function issue(): BelongsTo { return $this->belongsTo(Issue::class); }
    public function actor(): BelongsTo { return $this->belongsTo(User::class, 'actor_id'); }
}
```

### 4.4 Update `LocationType` enum

```php
enum LocationType: string
{
    case Lobby = 'lobby';
    case Office = 'office';
    case Kitchen = 'kitchen';
    case Bathroom = 'bathroom';
    case Corridor = 'corridor';
    case Storage = 'storage';
    case Other = 'other';

    public function label(): string { return match($this) { ... }; }
    public function color(): string { return match($this) { ... }; }
}
```

---

## 5. Routes

```php
// Public scan (no auth required)
Route::get('/r/{uuid}', [PublicScanController::class, 'show'])->name('scan.show');

// Issue tracking (no auth required)
Route::get('/track/{tracking_code}', [IssueTrackController::class, 'show'])->name('track.show');

// Supervisor issue management
Route::middleware(['auth', 'role:supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/issues', [SupervisorIssueController::class, 'index'])->name('issues.index');
    Route::post('/issues/{issue}/assign', [SupervisorIssueController::class, 'assign'])->name('issues.assign');
    Route::post('/issues/{issue}/status', [SupervisorIssueController::class, 'updateStatus'])->name('issues.status');
});

// Staff issue view
Route::middleware(['auth', 'role:staff'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/issues', [StaffIssueController::class, 'index'])->name('issues.index');
    Route::get('/issues/{issue}', [StaffIssueController::class, 'show'])->name('issues.show');
    Route::post('/issues/{issue}/status', [StaffIssueController::class, 'updateStatus'])->name('issues.status');
});
```

---

## 6. Controllers

### 6.1 `PublicScanController` (NEW)

```
GET /r/{uuid}
```

**Logic:**
1. Find location by UUID (404 if not found)
2. If location NOT configured → redirect to `/r/{uuid}/setup` (supervisor only)
3. If location IS configured → show public page with room info + "Report Issue" button

```php
class PublicScanController extends Controller
{
    public function show(string $uuid)
    {
        $location = Location::where('uuid', $uuid)->firstOrFail();

        if (!$location->configured) {
            // Only supervisor can configure
            if (auth()->check() && auth()->user()->isSupervisor()) {
                return redirect()->route('scan.setup', $uuid);
            }
            return view('pages.scan.not-configured');
        }

        return view('pages.scan.show', ['location' => $location]);
    }
}
```

### 6.2 `ScanSetupController` (NEW)

```
GET /r/{uuid}/setup     → form (supervisor only)
POST /r/{uuid}/setup    → save (supervisor only)
```

**Logic:**
1. Supervisor scans blank QR → redirected here
2. Shows simple form: Room Number + Type dropdown
3. On save: updates location `name`, `type`, `configured = true`, `configured_by = auth()->id()`

### 6.3 `IssueReportController` (NEW)

```
GET /r/{uuid}/report    → issue form
POST /r/{uuid}/report   → submit issue
```

**Logic:**
1. Anonymous/visitor fills: description (required), photo (optional)
2. Creates issue with `tracking_code` (SP-XXXXXX), `status = 'reported'`
3. Redirects to tracking page with the code

### 6.4 `IssueTrackController` (NEW)

```
GET /track/{tracking_code}
```

**Logic:**
1. Find issue by tracking code
2. Show status timeline (reported → assigned → in_progress → resolved)
3. No internal data exposed

### 6.5 `SupervisorIssueController` (NEW)

```
GET  /supervisor/issues              → list all issues for supervisor's building
POST /supervisor/issues/{id}/assign  → assign worker
POST /supervisor/issues/{id}/status  → change status
```

### 6.6 `StaffIssueController` (NEW)

```
GET  /staff/issues           → list assigned issues
GET  /staff/issues/{id}      → view issue details
POST /staff/issues/{id}/status → update status (in_progress → resolved)
```

---

## 7. Views

### 7.1 `pages/scan/show.blade.php` (Public — Mobile First)

```
┌──────────────────────────┐
│  📍 Room 101             │
│  Type: Lobby             │
│                          │
│  ┌────────────────────┐  │
│  │ 🛠 Report Issue    │  │
│  └────────────────────┘  │
└──────────────────────────┘
```

### 7.2 `pages/scan/setup.blade.php` (Supervisor — First Scan)

```
┌──────────────────────────┐
│  ⚙️ Configure This QR    │
│                          │
│  Room Number: [ 101 ]    │
│  Type: [ Lobby     ▼]    │
│                          │
│  [ Save & Activate ]     │
└──────────────────────────┘
```

### 7.3 `pages/scan/report.blade.php` (Anonymous — Issue Form)

```
┌──────────────────────────┐
│  📍 Room 101 — Lobby     │
│                          │
│  What's the problem?     │
│  ┌────────────────────┐  │
│  │                    │  │
│  │  (description)     │  │
│  │                    │  │
│  └────────────────────┘  │
│                          │
│  📷 Add Photo (optional) │
│                          │
│  [ Submit Report ]       │
└──────────────────────────┘
```

### 7.4 `pages/scan/tracking.blade.php` (Public — Status)

```
┌──────────────────────────┐
│  Track: SP-A1B2C3        │
│                          │
│  ● Reported    Jun 27    │
│  ○ Assigned               │
│  ○ In Progress            │
│  ○ Resolved               │
│                          │
│  Room 101 — Lobby        │
└──────────────────────────┘
```

### 7.5 `livewire/supervisor/issue-dashboard.blade.php`

Simple list of issues, grouped by status. Click to assign worker.

### 7.6 `livewire/staff/issue-list.blade.php`

Worker sees their assigned issues. Click to view details and update status.

---

## 8. Admin QR Generation (Simplified)

The admin flow becomes:

1. Admin enters building name → clicks "Generate QR Codes"
2. System creates N blank locations (just building name, no room info)
3. Admin downloads PDF of blank QR stickers
4. Done — supervisor handles the rest

**No complex location tree. No hierarchy. Just blank QRs.**

---

## 9. Implementation Order

| Step | What | Depends On | Est. Lines |
|---|---|---|---|
| 1 | Migration: add `configured` columns to locations | Nothing | 25 |
| 2 | Migration: create `issues` table | Nothing | 30 |
| 3 | Migration: create `issue_events` table | Nothing | 25 |
| 4 | Update `Location` model (remove hierarchy, add configured) | Step 1 | 30 |
| 5 | Create `Issue` model | Step 2 | 40 |
| 6 | Create `IssueEvent` model | Step 3 | 20 |
| 7 | Update `LocationType` enum (new types) | Nothing | 20 |
| 8 | `PublicScanController` + routes | Step 4 | 40 |
| 9 | `ScanSetupController` + routes | Step 4, 8 | 50 |
| 10 | `pages/scan/show.blade.php` | Step 8 | 40 |
| 11 | `pages/scan/setup.blade.php` | Step 9 | 50 |
| 12 | `pages/scan/not-configured.blade.php` | Step 8 | 20 |
| 13 | `IssueReportController` + routes | Step 5, 8 | 50 |
| 14 | `pages/scan/report.blade.php` | Step 13 | 60 |
| 15 | `pages/scan/tracking.blade.php` | Step 5 | 40 |
| 16 | `IssueTrackController` + route | Step 5 | 30 |
| 17 | `SupervisorIssueController` + routes | Step 5 | 60 |
| 18 | `livewire/supervisor/issue-dashboard.blade.php` | Step 17 | 80 |
| 19 | `StaffIssueController` + routes | Step 5 | 50 |
| 20 | `livewire/staff/issue-list.blade.php` | Step 19 | 60 |
| 21 | Simplify admin QR generation (blank QRs) | Step 4 | 40 |
| 22 | Update supervisor sidebar (issues link) | Step 17 | 10 |
| 23 | Tests | All above | 200 |
| **Total** | | | **~1,030** |

---

## 10. Test Cases

### Anonymous Scan Flow
- TC-A1: `GET /r/{uuid}` with configured location → 200, shows room info
- TC-A2: `GET /r/{uuid}` with blank location → redirects to setup (if supervisor) or shows "not configured"
- TC-A3: `GET /r/{uuid}` with invalid UUID → 404
- TC-A4: `GET /r/{uuid}/report` → 200, shows issue form
- TC-A5: `POST /r/{uuid}/report` with description → creates issue, redirects to tracking
- TC-A6: `POST /r/{uuid}/report` without description → validation error
- TC-A7: `GET /track/{code}` with valid code → 200, shows timeline
- TC-A8: `GET /track/{code}` with invalid code → 404

### Supervisor Setup Flow
- TC-S1: Supervisor scans blank QR → redirected to setup page
- TC-S2: Non-supervisor scans blank QR → sees "not configured" page
- TC-S3: Supervisor fills room number + type → location saved as configured
- TC-S4: Supervisor cannot configure already-configured QR → shows existing info

### Supervisor Issue Management
- TC-M1: Supervisor sees issues for their building
- TC-M2: Supervisor assigns worker to issue
- TC-M3: Supervisor changes issue status
- TC-M4: Non-supervisor cannot access supervisor issue routes

### Staff Issue Flow
- TC-W1: Staff sees list of assigned issues
- TC-W2: Staff views issue details
- TC-W3: Staff updates issue status (in_progress → resolved)
- TC-W4: Staff cannot see issues not assigned to them

---

## 11. Files Summary

| File | Action | Est. Lines |
|---|---|---|
| `database/migrations/xxxx_add_configured_to_locations.php` | Create | 25 |
| `database/migrations/xxxx_create_issues_table.php` | Create | 30 |
| `database/migrations/xxxx_create_issue_events_table.php` | Create | 25 |
| `app/Models/Location.php` | Modify | -30 |
| `app/Models/Issue.php` | Create | 40 |
| `app/Models/IssueEvent.php` | Create | 20 |
| `app/Enums/LocationType.php` | Modify | 20 |
| `app/Http/Controllers/PublicScanController.php` | Create | 40 |
| `app/Http/Controllers/ScanSetupController.php` | Create | 50 |
| `app/Http/Controllers/IssueReportController.php` | Create | 50 |
| `app/Http/Controllers/IssueTrackController.php` | Create | 30 |
| `app/Http/Controllers/SupervisorIssueController.php` | Create | 60 |
| `app/Http/Controllers/StaffIssueController.php` | Create | 50 |
| `resources/views/pages/scan/show.blade.php` | Create | 40 |
| `resources/views/pages/scan/setup.blade.php` | Create | 50 |
| `resources/views/pages/scan/not-configured.blade.php` | Create | 20 |
| `resources/views/pages/scan/report.blade.php` | Create | 60 |
| `resources/views/pages/scan/tracking.blade.php` | Create | 40 |
| `resources/views/livewire/supervisor/issue-dashboard.blade.php` | Create | 80 |
| `resources/views/livewire/staff/issue-list.blade.php` | Create | 60 |
| `routes/web.php` | Modify | +30 |
| `resources/views/layouts/supervisor.blade.php` | Modify | +5 |
| `tests/Feature/ScanFlowTest.php` | Create | 150 |
| `tests/Feature/IssueManagementTest.php` | Create | 150 |
| **Total** | | **~1,030** |

---

## 12. Conventions

1. **QR URLs use uuid**, never numeric IDs
2. **Tracking codes** are `SP-` + 6 random chars, unique
3. **Every status change** writes an `issue_events` row
4. **Anonymous** = no auth required; `reported_by` is null
5. **Mobile-first** for all public pages
6. **Tailwind tokens**: `primary`, `success`, `warning`, `error`, `surface-low`, `surface-high`
7. **No file exceeds 300 lines**
