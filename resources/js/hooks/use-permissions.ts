import { usePage } from '@inertiajs/react';

/**
 * Read the current user's permission names (shared from the server) and check
 * them. Super Admin is granted every permission server-side, so no special case
 * is needed here.
 */
export function usePermissions(): {
    permissions: string[];
    can: (permission: string) => boolean;
} {
    const permissions = usePage().props.auth.permissions;

    return {
        permissions,
        can: (permission: string): boolean => permissions.includes(permission),
    };
}
