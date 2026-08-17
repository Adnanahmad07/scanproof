<?php

namespace App\Enums;

enum IssueStatus: string
{
    case Reported = 'reported';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Reported => 'Reported',
            self::Assigned => 'Assigned',
            self::InProgress => 'In Progress',
            self::Resolved => 'Resolved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Reported => 'warning',
            self::Assigned => 'info',
            self::InProgress => 'primary',
            self::Resolved => 'success',
            self::Rejected => 'error',
        };
    }
}
