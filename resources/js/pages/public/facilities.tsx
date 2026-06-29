import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Sport = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
};

type Props = {
    page: { title: string; body: string };
    sports: Sport[];
    amenities: string[];
};

export default function Facilities({ page, sports, amenities }: Props) {
    return (
        <>
            <Head title="Facilities" />

            <div className="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    {page.title}
                </h1>
                {page.body && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {page.body}
                    </p>
                )}

                <div className="mt-8 grid gap-4 sm:grid-cols-2">
                    {sports.map((sport) => (
                        <Card key={sport.id}>
                            <CardContent className="flex items-center gap-4 p-5">
                                <div className="flex size-12 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-lg font-bold text-primary">
                                    {sport.name.charAt(0)}
                                </div>
                                <div>
                                    <div className="font-semibold">
                                        {sport.name}
                                    </div>
                                    <div className="text-xs text-muted-foreground">
                                        Lines &amp; equipment provided
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="mt-10">
                    <h2 className="text-sm font-semibold">Amenities</h2>
                    <ul className="mt-3 flex flex-wrap gap-2">
                        {amenities.map((amenity) => (
                            <li
                                key={amenity}
                                className="rounded-full border border-border px-3 py-1 text-sm text-muted-foreground"
                            >
                                {amenity}
                            </li>
                        ))}
                    </ul>
                </div>

                <div className="mt-10">
                    <Button asChild>
                        <Link href="/book">Book the court</Link>
                    </Button>
                </div>
            </div>
        </>
    );
}
