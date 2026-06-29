import { Head, Link } from '@inertiajs/react';
import { ClubStatusBadge } from '@/components/club-status-badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Membership = {
    id: number;
    club: string;
    role: string | null;
    status: string;
    membership_fee: string | null;
    fee_paid: boolean;
    applied_at: string | null;
};

type Props = {
    memberships: Membership[];
};

export default function Membership({ memberships }: Props) {
    return (
        <>
            <Head title="My Membership" />

            <div className="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    My Membership
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Track the status of your club applications.
                </p>

                {memberships.length === 0 ? (
                    <Card className="mt-8">
                        <CardContent className="p-8 text-center">
                            <p className="text-sm text-muted-foreground">
                                You haven&apos;t applied to any club yet.
                            </p>
                            <Button asChild className="mt-4">
                                <Link href="/clubs">Browse clubs</Link>
                            </Button>
                        </CardContent>
                    </Card>
                ) : (
                    <div className="mt-8 space-y-4">
                        {memberships.map((membership) => (
                            <Card key={membership.id}>
                                <CardContent className="p-5">
                                    <div className="flex items-center justify-between gap-2">
                                        <h2 className="font-semibold">
                                            {membership.club}
                                        </h2>
                                        <ClubStatusBadge
                                            status={membership.status}
                                        />
                                    </div>
                                    <dl className="mt-3 grid grid-cols-2 gap-2 text-sm sm:grid-cols-3">
                                        <div>
                                            <dt className="text-muted-foreground">
                                                Role
                                            </dt>
                                            <dd>{membership.role ?? '—'}</dd>
                                        </div>
                                        <div>
                                            <dt className="text-muted-foreground">
                                                Applied
                                            </dt>
                                            <dd>
                                                {membership.applied_at ?? '—'}
                                            </dd>
                                        </div>
                                        {membership.membership_fee && (
                                            <div>
                                                <dt className="text-muted-foreground">
                                                    Membership fee
                                                </dt>
                                                <dd>
                                                    ₱{membership.membership_fee}{' '}
                                                    ·{' '}
                                                    {membership.fee_paid
                                                        ? 'Paid'
                                                        : 'Not yet paid'}
                                                </dd>
                                            </div>
                                        )}
                                    </dl>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
