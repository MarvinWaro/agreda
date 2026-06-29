import { Head, router, useForm } from '@inertiajs/react';
import { Plus, ShieldCheck, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';

type Role = {
    id: number;
    name: string;
    permissions: string[];
    users_count: number;
    protected: boolean;
};

type Props = {
    roles: Role[];
    permissions: string[];
};

const permissionLabels: Record<string, string> = {
    'admin.access': 'Access the admin area',
    'bookings.view': 'View bookings',
    'bookings.manage': 'Confirm / decline bookings',
    'content.manage': 'Manage content (slides, events, FAQs, pages)',
    'sports.manage': 'Manage sports & rates',
    'settings.manage': 'Manage settings',
    'users.manage': 'Manage users',
    'roles.manage': 'Manage roles & permissions',
    'clubs.manage': 'Manage clubs & membership applications',
};

function label(permission: string): string {
    return permissionLabels[permission] ?? permission;
}

function PermissionGrid({
    catalog,
    selected,
    disabled = false,
    onToggle,
}: {
    catalog: string[];
    selected: string[];
    disabled?: boolean;
    onToggle?: (permission: string, checked: boolean) => void;
}) {
    return (
        <div className="grid gap-2 sm:grid-cols-2">
            {catalog.map((permission) => (
                <label
                    key={permission}
                    className={cn(
                        'flex items-center gap-2 text-sm',
                        disabled && 'text-muted-foreground',
                    )}
                >
                    <Checkbox
                        checked={selected.includes(permission)}
                        disabled={disabled}
                        onCheckedChange={(checked) =>
                            onToggle?.(permission, checked === true)
                        }
                    />
                    {label(permission)}
                </label>
            ))}
        </div>
    );
}

function RoleCard({
    role,
    catalog,
    onDelete,
}: {
    role: Role;
    catalog: string[];
    onDelete?: () => void;
}) {
    const form = useForm<{ permissions: string[] }>({
        permissions: role.permissions,
    });

    const toggle = (permission: string, checked: boolean) => {
        form.setData(
            'permissions',
            checked
                ? [...form.data.permissions, permission]
                : form.data.permissions.filter((value) => value !== permission),
        );
    };

    return (
        <Card>
            <CardHeader className="flex-row items-center justify-between space-y-0">
                <CardTitle className="text-base">{role.name}</CardTitle>
                <div className="flex items-center gap-3">
                    <span className="text-xs text-muted-foreground">
                        {role.users_count} user
                        {role.users_count === 1 ? '' : 's'}
                    </span>
                    {onDelete && (
                        <Button
                            size="icon"
                            variant="ghost"
                            className="size-8 text-muted-foreground hover:text-destructive"
                            onClick={onDelete}
                            aria-label={`Delete ${role.name}`}
                        >
                            <Trash2 className="size-4" />
                        </Button>
                    )}
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                {role.protected ? (
                    <>
                        <PermissionGrid
                            catalog={catalog}
                            selected={catalog}
                            disabled
                        />
                        <p className="text-xs text-muted-foreground">
                            The Super Admin role always has every permission.
                        </p>
                    </>
                ) : (
                    <>
                        <PermissionGrid
                            catalog={catalog}
                            selected={form.data.permissions}
                            onToggle={toggle}
                        />
                        <Button
                            size="sm"
                            disabled={form.processing || !form.isDirty}
                            onClick={() =>
                                form.put(`/admin/roles/${role.id}`, {
                                    preserveScroll: true,
                                })
                            }
                        >
                            Save permissions
                        </Button>
                    </>
                )}
            </CardContent>
        </Card>
    );
}

function NewRoleDialog({
    catalog,
    onClose,
}: {
    catalog: string[];
    onClose: () => void;
}) {
    const form = useForm<{ name: string; permissions: string[] }>({
        name: '',
        permissions: [],
    });

    const toggle = (permission: string, checked: boolean) => {
        form.setData(
            'permissions',
            checked
                ? [...form.data.permissions, permission]
                : form.data.permissions.filter((value) => value !== permission),
        );
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/admin/roles', {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    return (
        <Dialog open onOpenChange={(value) => !value && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>New role</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="role-name">Role name</Label>
                        <Input
                            id="role-name"
                            value={form.data.name}
                            onChange={(event) =>
                                form.setData('name', event.target.value)
                            }
                            required
                        />
                        {form.errors.name && (
                            <p className="text-sm text-destructive">
                                {form.errors.name}
                            </p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label>Permissions</Label>
                        <PermissionGrid
                            catalog={catalog}
                            selected={form.data.permissions}
                            onToggle={toggle}
                        />
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            Create role
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminRoles({ roles, permissions }: Props) {
    const [creating, setCreating] = useState(false);
    const [deleteTarget, setDeleteTarget] = useState<Role | null>(null);

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/roles/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <>
            <Head title="Admin · Roles & permissions" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-xl font-bold tracking-tight">
                            <ShieldCheck className="size-5 text-primary" />
                            Roles &amp; permissions
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Control what each role can do in the admin area.
                        </p>
                    </div>
                    <Button onClick={() => setCreating(true)}>
                        <Plus className="size-4" /> New role
                    </Button>
                </div>

                <div className="grid gap-4">
                    {roles.map((role) => (
                        <RoleCard
                            key={role.id}
                            role={role}
                            catalog={permissions}
                            onDelete={
                                role.protected
                                    ? undefined
                                    : () => setDeleteTarget(role)
                            }
                        />
                    ))}
                </div>
            </div>

            {creating && (
                <NewRoleDialog
                    catalog={permissions}
                    onClose={() => setCreating(false)}
                />
            )}

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(value) => !value && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete role?</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        Users assigned to {deleteTarget?.name} will lose its
                        permissions. This can&apos;t be undone.
                    </p>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteTarget(null)}
                        >
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
