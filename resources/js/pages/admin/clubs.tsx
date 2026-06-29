import { Head, Link, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2, Users } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type ClubRow = {
    id: number;
    name: string;
    slug: string;
    sport: string | null;
    description: string | null;
    membership_fee: string | null;
    is_active: boolean;
    members_count: number;
    roles_count: number;
};

type Sport = { id: number; name: string };

type Props = {
    clubs: ClubRow[];
    sports: Sport[];
};

function ClubDialog({
    sports,
    club,
    onClose,
}: {
    sports: Sport[];
    club: ClubRow | 'new';
    onClose: () => void;
}) {
    const editing = club !== 'new';
    const form = useForm({
        name: editing ? club.name : '',
        sport_id: editing
            ? (sports.find((sport) => sport.name === club.sport)?.id.toString() ??
              '')
            : '',
        description: editing ? (club.description ?? '') : '',
        membership_fee: editing ? (club.membership_fee ?? '') : '',
        is_active: editing ? club.is_active : true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = {
            preserveScroll: true,
            onSuccess: () => onClose(),
        };

        if (editing) {
            form.put(`/admin/clubs/${club.id}`, options);
        } else {
            form.post('/admin/clubs', options);
        }
    };

    return (
        <Dialog open onOpenChange={(value) => !value && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {editing ? 'Edit club' : 'New club'}
                    </DialogTitle>
                </DialogHeader>

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="club-name">Name</Label>
                        <Input
                            id="club-name"
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
                        <Label htmlFor="club-sport">Sport</Label>
                        <Select
                            value={form.data.sport_id || 'none'}
                            onValueChange={(value) =>
                                form.setData(
                                    'sport_id',
                                    value === 'none' ? '' : value,
                                )
                            }
                        >
                            <SelectTrigger id="club-sport" className="w-full">
                                <SelectValue placeholder="None" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="none">None</SelectItem>
                                {sports.map((sport) => (
                                    <SelectItem
                                        key={sport.id}
                                        value={String(sport.id)}
                                    >
                                        {sport.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="club-description">Description</Label>
                        <textarea
                            id="club-description"
                            value={form.data.description}
                            onChange={(event) =>
                                form.setData(
                                    'description',
                                    event.target.value,
                                )
                            }
                            rows={3}
                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>

                    <div className="space-y-2">
                        <Label htmlFor="club-membership-fee">
                            Membership fee (₱, optional)
                        </Label>
                        <Input
                            id="club-membership-fee"
                            type="number"
                            min={0}
                            step="0.01"
                            value={form.data.membership_fee}
                            onChange={(event) =>
                                form.setData(
                                    'membership_fee',
                                    event.target.value,
                                )
                            }
                        />
                        {form.errors.membership_fee && (
                            <p className="text-sm text-destructive">
                                {form.errors.membership_fee}
                            </p>
                        )}
                    </div>

                    <label className="flex items-center gap-2 text-sm">
                        <Checkbox
                            checked={form.data.is_active}
                            onCheckedChange={(checked) =>
                                form.setData('is_active', checked === true)
                            }
                        />
                        Open for applications
                    </label>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {editing ? 'Save changes' : 'Create club'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminClubs({ clubs, sports }: Props) {
    const [formClub, setFormClub] = useState<ClubRow | 'new' | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<ClubRow | null>(null);

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/clubs/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <>
            <Head title="Admin · Clubs" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="flex items-center gap-2 text-xl font-bold tracking-tight">
                            <Users className="size-5 text-primary" />
                            Clubs
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Manage clubs, their officer roles, and membership
                            applications.
                        </p>
                    </div>
                    <Button onClick={() => setFormClub('new')}>
                        <Plus className="size-4" /> New club
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Sport</TableHead>
                                    <TableHead>Fee</TableHead>
                                    <TableHead>Members</TableHead>
                                    <TableHead>Roles</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {clubs.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No clubs yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    clubs.map((club) => (
                                        <TableRow key={club.id}>
                                            <TableCell className="font-medium">
                                                <Link
                                                    href={`/admin/clubs/${club.id}`}
                                                    className="hover:underline"
                                                >
                                                    {club.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {club.sport ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {club.membership_fee
                                                    ? `₱${club.membership_fee}`
                                                    : '—'}
                                            </TableCell>
                                            <TableCell>
                                                {club.members_count}
                                            </TableCell>
                                            <TableCell>
                                                {club.roles_count}
                                            </TableCell>
                                            <TableCell>
                                                <span
                                                    className={
                                                        club.is_active
                                                            ? 'text-emerald-600 dark:text-emerald-400'
                                                            : 'text-muted-foreground'
                                                    }
                                                >
                                                    {club.is_active
                                                        ? 'Open'
                                                        : 'Closed'}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1.5">
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() =>
                                                            setFormClub(club)
                                                        }
                                                        aria-label={`Edit ${club.name}`}
                                                    >
                                                        <Pencil className="size-3.5" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="outline"
                                                        onClick={() =>
                                                            setDeleteTarget(
                                                                club,
                                                            )
                                                        }
                                                        aria-label={`Delete ${club.name}`}
                                                    >
                                                        <Trash2 className="size-3.5" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            {formClub !== null && (
                <ClubDialog
                    key={formClub === 'new' ? 'new' : formClub.id}
                    sports={sports}
                    club={formClub}
                    onClose={() => setFormClub(null)}
                />
            )}

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(value) => !value && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete club?</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        {deleteTarget?.name} and its roles and members will be
                        permanently removed. This can&apos;t be undone.
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
