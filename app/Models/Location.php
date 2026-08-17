<?php

namespace App\Models;

use App\Enums\LocationType;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'parent_id',
        'building',
        'floor',
        'notes',
        'configured',
        'configured_by',
        'configured_at',
        'created_by',
        'supervisor_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => LocationType::class,
            'configured' => 'boolean',
            'configured_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Location $location) {
            if (empty($location->uuid)) {
                $location->uuid = (string) Str::uuid();
            }
            if (!empty($location->name)) {
                $location->configured = true;
                $location->configured_at = $location->configured_at ?? now();
            }
        });
    }

    // ── Relationships ──

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Location::class, 'parent_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function supervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function configuredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'configured_by');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function activeTasks(): HasMany
    {
        return $this->hasMany(Task::class)
            ->whereNotIn('status', [TaskStatus::Completed, TaskStatus::Verified]);
    }

    public function issues(): HasMany
    {
        return $this->hasMany(Issue::class);
    }

    public function scanVisits(): HasMany
    {
        return $this->hasMany(ScanVisit::class);
    }

    // ── Scopes ──

    public function scopeRoots(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOfType(Builder $query, LocationType|string $type): Builder
    {
        return $query->where('type', $type instanceof LocationType ? $type->value : $type);
    }

    public function scopeForSupervisor(Builder $query, int $userId): Builder
    {
        return $query->where('supervisor_id', $userId)
                     ->orWhere('created_by', $userId);
    }

    public function scopeConfigured(Builder $query): Builder
    {
        return $query->where('configured', true);
    }

    public function scopeBlank(Builder $query): Builder
    {
        return $query->where('configured', false);
    }

    // ── Helpers ──

    /**
     * Public scan URL — always uuid, never the numeric id.
     */
    public function scanUrl(): string
    {
        return url('/r/' . $this->uuid);
    }

    /**
     * Configure this location with room details (first scan by supervisor).
     */
    public function configure(string $name, LocationType $type, int $userId): void
    {
        $this->update([
            'name' => $name,
            'type' => $type,
            'configured' => true,
            'configured_by' => $userId,
            'configured_at' => now(),
        ]);
    }
}
