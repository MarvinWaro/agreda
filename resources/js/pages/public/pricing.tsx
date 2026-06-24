import { Head } from '@inertiajs/react';
import { Alert, AlertDescription } from '@/components/ui/alert';
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
    rate_offpeak: string;
    rate_peak: string;
};

export default function Pricing({ sports }: { sports: Sport[] }) {
    return (
        <>
            <Head title="Pricing" />

            <div className="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    Court rates
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Hourly rates per sport. Peak covers evenings and weekends.
                </p>

                <div className="mt-6 overflow-hidden rounded-lg border border-border">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Sport</TableHead>
                                <TableHead className="text-right">
                                    Off-peak
                                </TableHead>
                                <TableHead className="text-right">
                                    Peak
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {sports.map((sport) => (
                                <TableRow key={sport.id}>
                                    <TableCell className="font-medium">
                                        {sport.name}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        ₱{sport.rate_offpeak}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        ₱{sport.rate_peak}
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </div>

                <Alert className="mt-6">
                    <AlertDescription>
                        Payment is made in person at the court — there is no
                        online payment. Prices are shown for reference.
                    </AlertDescription>
                </Alert>
            </div>
        </>
    );
}
