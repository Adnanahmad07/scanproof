<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class SupervisorInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'token',
        'invited_by',
        'expires_at',
        'accepted_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast() || $this->status === 'expired';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isValidForUse(): bool
    {
        return $this->status === 'pending' && $this->expires_at->isFuture();
    }

    public function markAccepted(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    public static function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public static function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere('expires_at', '<', now());
    }

    public static function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }
}
