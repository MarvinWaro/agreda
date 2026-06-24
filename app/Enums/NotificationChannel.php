<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Facebook = 'facebook';
    case Admin = 'admin';
    case Email = 'email';

    public function label(): string
    {
        return match ($this) {
            self::Facebook => 'Facebook',
            self::Admin => 'Admin alert',
            self::Email => 'Email',
        };
    }
}
