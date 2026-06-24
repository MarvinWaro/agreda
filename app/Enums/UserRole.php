<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Owner = 'owner';
    case Staff = 'staff';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::Owner => 'Owner',
            self::Staff => 'Staff',
        };
    }

    /**
     * Whether this role may access the admin panel.
     */
    public function canAccessAdmin(): bool
    {
        return match ($this) {
            self::Admin, self::Owner, self::Staff => true,
        };
    }
}
