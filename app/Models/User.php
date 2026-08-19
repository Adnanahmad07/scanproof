<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'organization_id',
        'supervisor_id',
        'can_print_qr',
        'is_active',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'can_print_qr' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organization(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function supervisor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    public function workers(): HasMany
    {
        return $this->hasMany(User::class, 'supervisor_id');
    }

    public function sentInvitations(): HasMany
    {
        return $this->hasMany(SupervisorInvitation::class, 'invited_by');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'supervisor_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function createdLocations(): HasMany
    {
        return $this->hasMany(Location::class, 'created_by');
    }

    public static function orgId(): ?int
    {
        return auth()->check() ? auth()->user()->organization_id : null;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isSupervisor(): bool
    {
        return $this->role === UserRole::Supervisor;
    }

    public function isStaff(): bool
    {
        return $this->role === UserRole::Staff;
    }

    public function isClient(): bool
    {
        return $this->role === UserRole::Client;
    }

    /**
     * Whether this user may print / download QR code sheets.
     * Admins always may; supervisors only when explicitly granted.
     */
    public function canPrintQr(): bool
    {
        return $this->isAdmin()
            || ($this->isSupervisor() && (bool) $this->can_print_qr);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
