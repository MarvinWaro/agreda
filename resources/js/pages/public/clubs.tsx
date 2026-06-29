import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Club = {
    id: number;
    name: string;
    slug: string;
    sport: string | null;
    description: string | null;
    membership_fee: string | null;
};

type Props = {
    clubs: Club[];
};

export default function Clubs({ clubs }: Props) {
    return (
        <>
            <Head title="Clubs" />

            <div className="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">Clubs</h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Join one of our member clubs — applications are reviewed
                    by the club officers.
                </p>

                {clubs.length === 0 ? (
                    <p className="mt-6 text-sm text-muted-foreground">
                        No clubs are open for applications right now.
                    </p>
                ) : (
                    <div className="mt-8 grid gap-4 sm:grid-cols-2">
                        {clubs.map((club) => (
                            <Card key={club.id}>
                                <CardContent className="p-5">
                                    <div className="flex items-center justify-between gap-2">
                                        <h2 className="font-semibold">
                                            {club.name}
                                        </h2>
                                        {club.sport && (
                                            <span className="rounded-full bg-primary/10 px-2.5 py-0.5 text-xs font-medium text-primary">
                                                {club.sport}
                                            </span>
                                        )}
                                    </div>
                                    {club.description && (
                                        <p className="mt-2 text-sm text-muted-foreground">
                                            {club.description}
                                        </p>
                                    )}
                                    {club.membership_fee && (
                                        <p className="mt-2 text-sm font-medium text-primary">
                                            ₱{club.membership_fee} lifetime
                                            membership fee
                                        </p>
                                    )}
                                    <Button asChild className="mt-4">
                                        <Link href={`/clubs/${club.slug}/join`}>
                                            Join
                                        </Link>
                                    </Button>
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}
