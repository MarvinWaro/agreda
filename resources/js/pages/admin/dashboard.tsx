import { Head } from '@inertiajs/react';
import { BookingActions } from '@/components/booking-actions';
import { BookingStatusBadge } from '@/components/booking-status-badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { cn } from '@/lib/utils';

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

type Props = {
    stats: { pending: number; today: number; week: number };
    latest: BookingRow[];
};

function StatTile({
    label,
    value,
    accent = false,
}: {
    label: string;
    value: number;
    accent?: boolean;
}) {
    return (
        <Card>
            <CardContent className="p-5">
                <div
                    className={cn(
                        'text-3xl font-bold tracking-tight',
                        accent ? 'text-primary' : 'text-foreground',
                    )}
                >
                    {value}
                </div>
                <div className="mt-1 text-sm text-muted-foreground">
                    {label}
                </div>
            </CardContent>
        </Card>
    );
}

export default function AdminDashboard({ stats, latest }: Props) {
    return (
        <>
            <Head title="Admin · Dashboard" />

            <div className="space-y-6 p-4 sm:p-6">
                <div>
                    <h1 className="text-xl font-bold tracking-tight">
                        Overview
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Court booking activity at a glance.
                    </p>
                </div>

                <div className="grid gap-4 sm:grid-cols-3">
                    <StatTile label="Pending" value={stats.pending} accent />
                    <StatTile label="Today" value={stats.today} />
                    <StatTile label="This week" value={stats.week} />
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">
                            Latest requests
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {latest.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                No pending requests right now.
                            </p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Name</TableHead>
                                        <TableHead>Slot</TableHead>
                                        <TableHead>Sport</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="text-right">
                                            Action
                                        </TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {latest.map((booking) => (
                                        <TableRow key={booking.id}>
                                            <TableCell className="font-medium">
                                                {booking.guest_name}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {booking.date} · {booking.time}
                                            </TableCell>
                                            <TableCell>
                                                {booking.sport}
                                            </TableCell>
                                            <TableCell>
                                                <BookingStatusBadge
                                                    status={booking.status}
                                                />
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end">
                                                    <BookingActions
                                                        id={booking.id}
                                                    />
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
