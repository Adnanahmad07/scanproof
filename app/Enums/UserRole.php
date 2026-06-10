<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Supervisor = 'supervisor';
    case Staff = 'staff';
    case Client = 'client';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'System Admin',
            self::Supervisor => 'Supervisor',
            self::Staff => 'Staff',
            self::Client => 'Client',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Admin => 'primary',
            self::Supervisor => 'info',
            self::Staff => 'success',
            self::Client => 'warning',
        };
    }

    public function dashboardPath(): string
    {
        return match ($this) {
            self::Admin => '/admin/dashboard',
            self::Supervisor => '/supervisor/dashboard',
            self::Staff => '/staff/dashboard',
            self::Client => '/client/dashboard',
        };
    }
}
