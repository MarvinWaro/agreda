import { Head, Link, router } from '@inertiajs/react';
import { ClubMemberActions } from '@/components/club-member-actions';
import { ClubStatusBadge } from '@/components/club-status-badge';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
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

type ClubRole = { id: number; name: string };

type ClubOption = {
    id: number;
    name: string;
    membership_fee: string | null;
    roles: ClubRole[];
};

type MemberRow = {
    id: number;
    club: string;
    club_id: number;
    club_role_id: number | null;
    club_role: string | null;
    full_name: string;
    age: number;
    sex: string;
    occupation: string;
    address: string;
    phone: string;
    notes: string | null;
    status: string;
    created_at: string | null;
    fee_paid: boolean;
};

type Paginated<T> = {
    data: T[];
    current_page: number;
    last_page: number;
    prev_page_url: string | null;
    next_page_url: string | null;
    from: number | null;
    to: number | null;
    total: number;
};

type Filters = {
    club_id: number | null;
    status: string | null;
};

type Props = {
    members: Paginated<MemberRow>;
    clubs: ClubOption[];
    statuses: { value: string; label: string }[];
    filters: Filters;
};

function RoleSelect({ member, clubs }: { member: MemberRow; clubs: ClubOption[] }) {
    const club = clubs.find((option) => option.id === member.club_id);
    const roles = club?.roles ?? [];

    const assign = (value: string) => {
        router.put(
            `/admin/club-members/${member.id}`,
            { club_role_id: value === 'none' ? null : Number(value) },
            { preserveScroll: true },
        );
    };

    return (
        <Select
            value={member.club_role_id ? String(member.club_role_id) : 'none'}
            onValueChange={assign}
        >
            <SelectTrigger className="w-40" aria-label={`${member.full_name} role`}>
                <SelectValue placeholder="No role" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="none">No role</SelectItem>
                {roles.map((role) => (
                    <SelectItem key={role.id} value={String(role.id)}>
                        {role.name}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
}

function FeeCheckbox({ member, clubs }: { member: MemberRow; clubs: ClubOption[] }) {
    const club = clubs.find((option) => option.id === member.club_id);

    if (!club?.membership_fee) {
        return <span className="text-sm text-muted-foreground">—</span>;
    }

    return (
        <label className="flex items-center gap-2 text-sm">
            <Checkbox
                checked={member.fee_paid}
                onCheckedChange={(checked) =>
                    router.put(
                        `/admin/club-members/${member.id}`,
                        { fee_paid: checked === true },
                        { preserveScroll: true },
                    )
                }
                aria-label={`${member.full_name} fee paid`}
            />
            ₱{club.membership_fee}
        </label>
    );
}

export default function AdminClubMembers({
    members,
    clubs,
    statuses,
    filters,
}: Props) {
    const applyFilters = (next: Partial<Filters>) => {
        const merged = { ...filters, ...next };

        router.get(
            '/admin/club-members',
            {
                club_id: merged.club_id ?? undefined,
                status: merged.status ?? undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Admin · Club membership applications" />

            <div className="space-y-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">
                            Membership applications
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {members.total} total · approve or decline pending
                            applications.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        <Select
                            value={
                                filters.club_id ? String(filters.club_id) : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilters({
                                    club_id:
                                        value === 'all' ? null : Number(value),
                                })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder="Club" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All clubs</SelectItem>
                                {clubs.map((club) => (
                                    <SelectItem
                                        key={club.id}
                                        value={String(club.id)}
                                    >
                                        {club.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Select
                            value={filters.status ?? 'all'}
                            onValueChange={(value) =>
                                applyFilters({
                                    status: value === 'all' ? null : value,
                                })
                            }
                        >
                            <SelectTrigger className="w-36">
                                <SelectValue placeholder="Status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">
                                    All statuses
                                </SelectItem>
                                {statuses.map((status) => (
                                    <SelectItem
                                        key={status.value}
                                        value={status.value}
                                    >
                                        {status.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Applicant</TableHead>
                                    <TableHead>Club</TableHead>
                                    <TableHead>Age</TableHead>
                                    <TableHead>Sex</TableHead>
                                    <TableHead>Occupation</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Role</TableHead>
                                    <TableHead>Fee</TableHead>
                                    <TableHead className="text-right">
                                        Action
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {members.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={10}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No applications match these
                                            filters.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    members.data.map((member) => (
                                        <TableRow key={member.id}>
                                            <TableCell className="font-medium">
                                                {member.full_name}
                                                <p className="text-xs text-muted-foreground">
                                                    {member.created_at}
                                                </p>
                                            </TableCell>
                                            <TableCell>
                                                {member.club}
                                            </TableCell>
                                            <TableCell>
                                                {member.age}
                                            </TableCell>
                                            <TableCell>
                                                {member.sex}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {member.occupation}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {member.phone}
                                            </TableCell>
                                            <TableCell>
                                                <ClubStatusBadge
                                                    status={member.status}
                                                />
                                            </TableCell>
                                            <TableCell>
                                                {member.status === 'pending' ? (
                                                    <span className="text-sm text-muted-foreground">
                                                        —
                                                    </span>
                                                ) : (
                                                    <RoleSelect
                                                        member={member}
                                                        clubs={clubs}
                                                    />
                                                )}
                                            </TableCell>
                                            <TableCell>
                                                <FeeCheckbox
                                                    member={member}
                                                    clubs={clubs}
                                                />
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end">
                                                    {member.status ===
                                                    'pending' ? (
                                                        <ClubMemberActions
                                                            id={member.id}
                                                        />
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">
                                                            —
                                                        </span>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <div className="flex items-center justify-between text-sm text-muted-foreground">
                    <span>
                        Page {members.current_page} of {members.last_page}
                    </span>
                    <div className="flex gap-2">
                        {members.prev_page_url ? (
                            <Link href={members.prev_page_url} preserveScroll>
                                Previous
                            </Link>
                        ) : (
                            <span className="opacity-50">Previous</span>
                        )}
                        {members.next_page_url ? (
                            <Link href={members.next_page_url} preserveScroll>
                                Next
                            </Link>
                        ) : (
                            <span className="opacity-50">Next</span>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
