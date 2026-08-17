<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Verified = 'verified';
    case Blocked = 'blocked';
    case Reopened = 'reopened';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Verified => 'Verified',
            self::Blocked => 'Blocked',
            self::Reopened => 'Reopened',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::InProgress => 'info',
            self::Completed => 'success',
            self::Verified => 'success',
            self::Blocked => 'error',
            self::Reopened => 'warning',
        };
    }
}
