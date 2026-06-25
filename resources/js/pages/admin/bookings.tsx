import { Head, Link, router } from '@inertiajs/react';
import { BookingActions } from '@/components/booking-actions';
import { BookingStatusBadge } from '@/components/booking-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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

type BookingRow = {
    id: number;
    date: string;
    date_full: string;
    time: string;
    sport: string;
    guest_name: string;
    guest_phone: string;
    status: string;
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
    status: string | null;
    sport_id: number | null;
    date: string | null;
};

type Props = {
    bookings: Paginated<BookingRow>;
    sports: { id: number; name: string }[];
    statuses: { value: string; label: string }[];
    filters: Filters;
};

export default function AdminBookings({
    bookings,
    sports,
    statuses,
    filters,
}: Props) {
    const applyFilters = (next: Partial<Filters>) => {
        const merged = { ...filters, ...next };

        router.get(
            '/admin/bookings',
            {
                status: merged.status ?? undefined,
                sport_id: merged.sport_id ?? undefined,
                date: merged.date || undefined,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    };

    return (
        <>
            <Head title="Admin · Bookings" />

            <div className="space-y-6 p-4 sm:p-6">
                <div className="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">
                            Booking requests
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {bookings.total} total · confirm or decline pending
                            requests.
                        </p>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
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

                        <Select
                            value={
                                filters.sport_id
                                    ? String(filters.sport_id)
                                    : 'all'
                            }
                            onValueChange={(value) =>
                                applyFilters({
                                    sport_id:
                                        value === 'all' ? null : Number(value),
                                })
                            }
                        >
                            <SelectTrigger className="w-36">
                                <SelectValue placeholder="Sport" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">All sports</SelectItem>
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

                        <Input
                            type="date"
                            value={filters.date ?? ''}
                            onChange={(event) =>
                                applyFilters({
                                    date: event.target.value || null,
                                })
                            }
                            className="w-40"
                        />
                    </div>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Time</TableHead>
                                    <TableHead>Sport</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Phone</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">
                                        Action
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {bookings.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={7}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No bookings match these filters.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    bookings.data.map((booking) => (
                                        <TableRow key={booking.id}>
                                            <TableCell>
                                                {booking.date_full}
                                            </TableCell>
                                            <TableCell>
                                                {booking.time}
                                            </TableCell>
                                            <TableCell>
                                                {booking.sport}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {booking.guest_name}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {booking.guest_phone}
                                            </TableCell>
                                            <TableCell>
                                                <BookingStatusBadge
                                                    status={booking.status}
                                                />
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end">
                                                    {booking.status ===
                                                    'pending' ? (
                                                        <BookingActions
                                                            id={booking.id}
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
                        Page {bookings.current_page} of {bookings.last_page}
                    </span>
                    <div className="flex gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            disabled={!bookings.prev_page_url}
                        >
                            {bookings.prev_page_url ? (
                                <Link
                                    href={bookings.prev_page_url}
                                    preserveScroll
                                >
                                    Previous
                                </Link>
                            ) : (
                                <span>Previous</span>
                            )}
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            asChild
                            disabled={!bookings.next_page_url}
                        >
                            {bookings.next_page_url ? (
                                <Link
                                    href={bookings.next_page_url}
                                    preserveScroll
                                >
                                    Next
                                </Link>
                            ) : (
                                <span>Next</span>
                            )}
                        </Button>
                    </div>
                </div>
            </div>
        </>
    );
}
