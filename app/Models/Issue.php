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
        return $query->whereHas('location', function ($q) use ($userId) {
            $q->where('supervisor_id', $userId)
              ->orWhereHas('parent', function ($pq) use ($userId) {
                  $pq->where('supervisor_id', $userId)
                     ->orWhereHas('parent', function ($ppq) use ($userId) {
                         $ppq->where('supervisor_id', $userId);
                     });
              });
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
