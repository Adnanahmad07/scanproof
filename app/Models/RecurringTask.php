<?php

namespace App\Models;

use App\Enums\TaskCategory;
use App\Enums\TaskPriority;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringTask extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'location',
        'location_id',
        'category',
        'priority',
        'frequency',
        'day_of_week',
        'day_of_month',
        'assigned_to',
        'supervisor_id',
        'is_active',
        'last_generated_at',
    ];

    protected function casts(): array
    {
        return [
            'category' => TaskCategory::class,
            'priority' => TaskPriority::class,
            'is_active' => 'boolean',
            'last_generated_at' => 'datetime',
        ];
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function generatedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'recurring_task_id');
    }

    public function shouldGenerateToday(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $today = now();

        if ($this->last_generated_at && $this->last_generated_at->isToday()) {
            return false;
        }

        return match ($this->frequency) {
            'daily' => true,
            'weekly' => $today->dayOfWeek === $this->day_of_week,
            'monthly' => $today->day === $this->day_of_month,
            default => false,
        };
    }

    public function generateTask(): Task
    {
        $task = Task::create([
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'location_id' => $this->location_id,
            'category' => $this->category,
            'priority' => $this->priority,
            'status' => 'pending',
            'supervisor_id' => $this->supervisor_id,
            'assigned_to' => $this->assigned_to,
            'recurring_task_id' => $this->id,
            'due_date' => now()->toDateString(),
        ]);

        $this->update(['last_generated_at' => now()]);

        return $task;
    }
}
