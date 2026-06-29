import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, Plus, Star, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { cn } from '@/lib/utils';

type Club = {
    id: number;
    name: string;
    slug: string;
    is_active: boolean;
    members_count: number;
};

type ClubRole = {
    id: number;
    name: string;
    is_default: boolean;
};

type Props = {
    club: Club;
    roles: ClubRole[];
};

function NewRoleDialog({
    club,
    onClose,
}: {
    club: Club;
    onClose: () => void;
}) {
    const form = useForm({ name: '', is_default: false });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/admin/clubs/${club.id}/roles`, {
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

function RoleRow({ club, role }: { club: Club; role: ClubRole }) {
    const form = useForm({ name: role.name, is_default: role.is_default });
    const [deleting, setDeleting] = useState(false);

    const save = () => {
        form.put(`/admin/clubs/${club.id}/roles/${role.id}`, {
            preserveScroll: true,
        });
    };

    const makeDefault = () => {
        router.put(
            `/admin/clubs/${club.id}/roles/${role.id}`,
            { name: role.name, is_default: true },
            { preserveScroll: true },
        );
    };

    const destroy = () => {
        router.delete(`/admin/clubs/${club.id}/roles/${role.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleting(false),
        });
    };

    return (
        <TableRow>
            <TableCell>
                <Input
                    value={form.data.name}
                    onChange={(event) =>
                        form.setData('name', event.target.value)
                    }
                    className="max-w-56"
                    aria-label={`${role.name} name`}
                />
            </TableCell>
            <TableCell>
                {role.is_default ? (
                    <span className="inline-flex items-center gap-1 text-sm font-medium text-primary">
                        <Star className="size-3.5 fill-current" /> Default
                    </span>
                ) : (
                    <Button size="sm" variant="outline" onClick={makeDefault}>
                        Make default
                    </Button>
                )}
            </TableCell>
            <TableCell className="text-right">
                <div className="flex justify-end gap-1.5">
                    <Button
                        size="sm"
                        onClick={save}
                        disabled={form.processing || !form.isDirty}
                    >
                        Save
                    </Button>
                    <Button
                        size="sm"
                        variant="outline"
                        disabled={role.is_default || deleting}
                        onClick={() => {
                            setDeleting(true);
                            destroy();
                        }}
                        aria-label={`Delete ${role.name}`}
                        title={
                            role.is_default
                                ? "Can't delete the default role"
                                : undefined
                        }
                    >
                        <Trash2 className="size-3.5" />
                    </Button>
                </div>
            </TableCell>
        </TableRow>
    );
}

export default function AdminClubDetail({ club, roles }: Props) {
    const [addingRole, setAddingRole] = useState(false);

    return (
        <>
            <Head title={`Admin · ${club.name}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <Button
                            asChild
                            variant="ghost"
                            size="sm"
                            className="-ml-2 mb-1"
                        >
                            <Link href="/admin/clubs">
                                <ArrowLeft className="size-4" /> Clubs
                            </Link>
                        </Button>
                        <h1 className="text-xl font-bold tracking-tight">
                            {club.name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            <span
                                className={cn(
                                    club.is_active
                                        ? 'text-emerald-600 dark:text-emerald-400'
                                        : undefined,
                                )}
                            >
                                {club.is_active ? 'Open' : 'Closed'}
                            </span>{' '}
                            · {club.members_count} member
                            {club.members_count === 1 ? '' : 's'}
                        </p>
                    </div>
                </div>

                <Tabs defaultValue="roles">
                    <TabsList>
                        <TabsTrigger value="roles">Roles</TabsTrigger>
                        <TabsTrigger value="members">Members</TabsTrigger>
                    </TabsList>

                    <TabsContent value="roles" className="space-y-4 pt-4">
                        <div className="flex items-center justify-between">
                            <p className="text-sm text-muted-foreground">
                                Officer roles applicants can be assigned to.
                            </p>
                            <Button
                                size="sm"
                                onClick={() => setAddingRole(true)}
                            >
                                <Plus className="size-4" /> New role
                            </Button>
                        </div>

                        <Card>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Name</TableHead>
                                            <TableHead>Default</TableHead>
                                            <TableHead className="text-right">
                                                Actions
                                            </TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {roles.length === 0 ? (
                                            <TableRow>
                                                <TableCell
                                                    colSpan={3}
                                                    className="py-10 text-center text-muted-foreground"
                                                >
                                                    No roles yet.
                                                </TableCell>
                                            </TableRow>
                                        ) : (
                                            roles.map((role) => (
                                                <RoleRow
                                                    key={role.id}
                                                    club={club}
                                                    role={role}
                                                />
                                            ))
                                        )}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    </TabsContent>

                    <TabsContent value="members" className="space-y-4 pt-4">
                        <p className="text-sm text-muted-foreground">
                            Review membership applications for this club.
                        </p>
                        <Button asChild>
                            <Link href={`/admin/club-members?club_id=${club.id}`}>
                                View {club.name} applications
                            </Link>
                        </Button>
                    </TabsContent>
                </Tabs>
            </div>

            {addingRole && (
                <NewRoleDialog club={club} onClose={() => setAddingRole(false)} />
            )}
        </>
    );
}
