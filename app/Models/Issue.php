<?php

namespace App\Models;

use App\Enums\IssueStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Issue extends Model
{
    use HasFactory;

    protected $fillable = [
        'tracking_code',
        'location_id',
        'description',
        'photo_path',
        'status',
        'rejection_reason',
        'assigned_to',
        'reported_by',
        'resolved_at',
        'organization_id',
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
                $issue->tracking_code = self::generateTrackingCode();
            }
        });
    }

    /**
     * Generate a unique tracking code like SP-A1B2C3.
     */
    public static function generateTrackingCode(): string
    {
        do {
            $code = 'SP-' . strtoupper(Str::random(6));
        } while (static::where('tracking_code', $code)->exists());

        return $code;
    }

    // ── Relationships ──

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function events(): HasMany
    {
        return $this->hasMany(IssueEvent::class);
    }

    public function task(): HasOne
    {
        return $this->hasOne(Task::class);
    }

    // ── Scopes ──

    public function scopeForSupervisor(Builder $query, int $userId): Builder
    {
        // Get worker IDs for this supervisor (family tree) within same org
        $workerIds = User::where('supervisor_id', $userId)
            ->where('organization_id', auth()->user()->organization_id)
            ->pluck('id')
            ->toArray();

        return $query->where(function ($q) use ($userId, $workerIds) {
            // 1. Issues at locations directly assigned to this supervisor
            $q->whereHas('location', function ($loc) use ($userId) {
                $loc->where('supervisor_id', $userId)
                    ->orWhereHas('parent', function ($parent) use ($userId) {
                        $parent->where('supervisor_id', $userId)
                            ->orWhereHas('parent', function ($grandparent) use ($userId) {
                                $grandparent->where('supervisor_id', $userId);
                            });
                    });
            });

            // 2. Issues at locations created by this supervisor
            $q->orWhereHas('location', function ($loc) use ($userId) {
                $loc->where('created_by', $userId);
            });

            // 3. Issues assigned to this supervisor's workers
            if (!empty($workerIds)) {
                $q->orWhereIn('assigned_to', $workerIds);
            }

            // 4. Issues reported by this supervisor's workers
            if (!empty($workerIds)) {
                $q->orWhereIn('reported_by', $workerIds);
            }
        });
    }

    public function scopeForStaff(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeReported(Builder $query): Builder
    {
        return $query->where('status', 'reported');
    }

    public function scopeAssigned(Builder $query): Builder
    {
        return $query->where('status', 'assigned');
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query): Builder
    {
        return $query->where('status', 'resolved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    // ── Status Transitions ──

    private const VALID_TRANSITIONS = [
        'reported' => ['assigned', 'rejected'],
        'assigned' => ['in_progress', 'reported', 'rejected'],
        'in_progress' => ['resolved', 'assigned', 'rejected'],
        'resolved' => [],
        'rejected' => [],
    ];

    public function canTransitionTo(string $newStatus): bool
    {
        $currentStatus = is_string($this->status) ? $this->status : $this->status->value;
        return in_array($newStatus, self::VALID_TRANSITIONS[$currentStatus] ?? [], true);
    }

    public function transitionTo(string $newStatus, ?int $actorId = null, ?string $note = null): void
    {
        $currentStatus = is_string($this->status) ? $this->status : $this->status->value;

        if (!$this->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from \"{$currentStatus}\" to \"{$newStatus}\"."
            );
        }

        $oldStatus = $currentStatus;

        $data = [
            'status' => $newStatus,
            'resolved_at' => $newStatus === 'resolved' ? now() : null,
        ];

        if ($newStatus === 'rejected') {
            $data['rejection_reason'] = $note;
        }

        $this->update($data);

        $this->events()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'actor_id' => $actorId,
            'note' => $note,
        ]);
    }
}
