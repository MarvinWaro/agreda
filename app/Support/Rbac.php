<?php

namespace App\Support;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Central definition of the application's roles and permissions.
 * Used by the seeder, the user factory and the Roles & Permissions admin UI.
 */
class Rbac
{
    public const SUPER_ADMIN = 'Super Admin';

    public const OWNER = 'Owner';

    public const STAFF = 'Staff';

    public const GUARD = 'web';

    /**
     * @var list<string>
     */
    public const PERMISSIONS = [
        'admin.access',
        'bookings.view',
        'bookings.manage',
        'content.manage',
        'sports.manage',
        'settings.manage',
        'users.manage',
        'roles.manage',
    ];

    /**
     * Role => the permissions granted to it. Super Admin also bypasses every
     * check via Gate::before, but is given all permissions for clarity in the UI.
     *
     * @var array<string, list<string>>
     */
    public const ROLES = [
        self::SUPER_ADMIN => self::PERMISSIONS,
        self::OWNER => [
            'admin.access',
            'bookings.view',
            'bookings.manage',
            'content.manage',
            'sports.manage',
            'settings.manage',
        ],
        self::STAFF => [
            'admin.access',
            'bookings.view',
            'bookings.manage',
        ],
    ];

    /**
     * Idempotently create every permission and role with its permission set.
     */
    public static function sync(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::findOrCreate($permission, self::GUARD);
        }

        // Re-read the freshly created permissions before assigning them.
        $registrar->forgetCachedPermissions();

        foreach (self::ROLES as $role => $permissions) {
            Role::findOrCreate($role, self::GUARD)->syncPermissions($permissions);
        }
    }
}
