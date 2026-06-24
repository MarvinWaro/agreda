import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Sport = {
    id: number;
    name: string;
    slug: string;
    rate_offpeak: string;
    rate_peak: string;
    is_active: boolean;
};

function SportRow({ sport }: { sport: Sport }) {
    const form = useForm({
        rate_offpeak: sport.rate_offpeak,
        rate_peak: sport.rate_peak,
        is_active: sport.is_active,
    });

    const save = () =>
        form.patch(`/admin/sports/${sport.id}`, { preserveScroll: true });

    return (
        <TableRow>
            <TableCell className="font-medium">{sport.name}</TableCell>
            <TableCell>
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    value={form.data.rate_offpeak}
                    onChange={(event) =>
                        form.setData('rate_offpeak', event.target.value)
                    }
                    className="w-28"
                    aria-label={`${sport.name} off-peak rate`}
                />
            </TableCell>
            <TableCell>
                <Input
                    type="number"
                    step="0.01"
                    min="0"
                    value={form.data.rate_peak}
                    onChange={(event) =>
                        form.setData('rate_peak', event.target.value)
                    }
                    className="w-28"
                    aria-label={`${sport.name} peak rate`}
                />
            </TableCell>
            <TableCell>
                <Checkbox
                    checked={form.data.is_active}
                    onCheckedChange={(checked) =>
                        form.setData('is_active', checked === true)
                    }
                    aria-label={`${sport.name} active`}
                />
            </TableCell>
            <TableCell className="text-right">
                <Button
                    size="sm"
                    onClick={save}
                    disabled={form.processing || !form.isDirty}
                >
                    Save
                </Button>
            </TableCell>
        </TableRow>
    );
}

export default function AdminSports({ sports }: { sports: Sport[] }) {
    return (
        <>
            <Head title="Admin · Sports & rates" />

            <div className="mx-auto w-full max-w-3xl space-y-6 p-4">
                <div>
                    <h1 className="text-xl font-bold tracking-tight">
                        Sports &amp; rates
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Set hourly rates and toggle which sports accept
                        bookings.
                    </p>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Sport</TableHead>
                                    <TableHead>Off-peak (₱)</TableHead>
                                    <TableHead>Peak (₱)</TableHead>
                                    <TableHead>Active</TableHead>
                                    <TableHead className="text-right">
                                        Save
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {sports.map((sport) => (
                                    <SportRow key={sport.id} sport={sport} />
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
