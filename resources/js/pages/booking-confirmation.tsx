import { Head, Link } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import PublicLayout from '@/layouts/public-layout';

type Props = {
    booking: {
        reference: number;
        sport: string;
        date: string;
        start: string;
        end: string;
        guest_name: string;
        status: string;
        total_price: string | null;
    };
};

function statusText(status: string): string {
    switch (status) {
        case 'pending':
            return 'Awaiting owner';
        case 'confirmed':
            return 'Confirmed';
        case 'declined':
            return 'Declined';
        case 'cancelled':
            return 'Cancelled';
        case 'completed':
            return 'Completed';
        default:
            return status;
    }
}

function Row({ label, value }: { label: string; value: string }) {
    return (
        <div className="flex items-center justify-between gap-4">
            <dt className="text-muted-foreground">{label}</dt>
            <dd className="text-right font-medium">{value}</dd>
        </div>
    );
}

export default function BookingConfirmation({ booking }: Props) {
    return (
        <PublicLayout>
            <Head title="Request submitted" />

            <div className="mx-auto w-full max-w-xl px-4 py-12 sm:px-6">
                <Card>
                    <CardContent className="p-8 text-center">
                        <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950">
                            <CircleCheck className="size-8" />
                        </div>

                        <h1 className="text-xl font-bold tracking-tight">
                            Request sent!
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            We&apos;ll confirm availability shortly.
                        </p>

                        <dl className="mt-6 space-y-2 rounded-lg border border-border p-4 text-left text-sm">
                            <Row
                                label="Reference"
                                value={`#${booking.reference}`}
                            />
                            <Row label="Sport" value={booking.sport} />
                            <Row label="Date" value={booking.date} />
                            <Row
                                label="Time"
                                value={`${booking.start} – ${booking.end}`}
                            />
                            <Row label="Name" value={booking.guest_name} />
                            {booking.total_price && (
                                <Row
                                    label="Estimated total"
                                    value={`₱${booking.total_price}`}
                                />
                            )}
                            <div className="flex items-center justify-between border-t border-border pt-2">
                                <dt className="text-muted-foreground">
                                    Status
                                </dt>
                                <dd className="font-semibold text-primary">
                                    {statusText(booking.status)}
                                </dd>
                            </div>
                        </dl>

                        <Alert className="mt-6 text-left">
                            <AlertTitle>What happens next</AlertTitle>
                            <AlertDescription>
                                The owner is notified via Facebook and the admin
                                dashboard. Payment is made in person at the
                                court.
                            </AlertDescription>
                        </Alert>

                        <div className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <Button asChild>
                                <Link href="/book">Book another slot</Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/">Back to home</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </PublicLayout>
    );
}
