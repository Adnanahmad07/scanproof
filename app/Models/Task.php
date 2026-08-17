<?php

namespace App\Models;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    const VALID_TRANSITIONS = [
        'pending' => ['in_progress', 'blocked'],
        'in_progress' => ['completed', 'blocked'],
        'completed' => ['verified', 'reopened'],
        'verified' => [],
        'blocked' => ['pending', 'reopened'],
        'reopened' => ['pending', 'in_progress'],
    ];

    protected $fillable = [
        'title',
        'description',
        'location',
        'location_id',
        'category',
        'priority',
        'status',
        'due_date',
        'supervisor_id',
        'assigned_to',
        'issue_id',
        'recurring_task_id',
    ];

    protected function casts(): array
    {
        return [
            'category' => TaskCategory::class,
            'priority' => TaskPriority::class,
            'status' => TaskStatus::class,
            'due_date' => 'date',
        ];
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(TaskPhoto::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function issue(): BelongsTo
    {
        return $this->belongsTo(Issue::class);
    }

    public function recurringTask(): BelongsTo
    {
        return $this->belongsTo(RecurringTask::class);
    }

    public function scopeForWorker($query, int $workerId)
    {
        return $query->where('assigned_to', $workerId);
    }

    public function scopeBySupervisor($query, int $supervisorId)
    {
        return $query->where('supervisor_id', $supervisorId);
    }

    public function scopeByLocation($query, int $locationId)
    {
        return $query->where('location_id', $locationId);
    }

    public function events(): HasMany
    {
        return $this->hasMany(TaskEvent::class);
    }

    public function transitionTo(TaskStatus $newStatus, ?int $actorId = null, ?string $note = null): void
    {
        $fromStatus = $this->status;

        if (!in_array($newStatus->value, self::VALID_TRANSITIONS[$fromStatus->value] ?? [])) {
            throw new \InvalidArgumentException(
                "Cannot transition from \"{$fromStatus->value}\" to \"{$newStatus->value}\"."
            );
        }

        $this->update(['status' => $newStatus]);

        TaskEvent::create([
            'task_id' => $this->id,
            'from_status' => $fromStatus->value,
            'to_status' => $newStatus->value,
            'actor_id' => $actorId,
            'note' => $note,
        ]);
    }

    public function hasPhoto(string $type): bool
    {
        return $this->photos()->where('type', $type)->exists();
    }
}
