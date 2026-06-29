import { Head, Link } from '@inertiajs/react';
import { CircleCheck } from 'lucide-react';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Props = {
    member: {
        full_name: string;
        club: string;
        status: string;
    };
};

export default function ClubJoinConfirmation({ member }: Props) {
    return (
        <>
            <Head title="Application submitted" />

            <div className="mx-auto w-full max-w-xl px-4 py-12 sm:px-6">
                <Card>
                    <CardContent className="p-8 text-center">
                        <div className="mx-auto mb-4 flex size-14 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 dark:bg-emerald-950">
                            <CircleCheck className="size-8" />
                        </div>

                        <h1 className="text-xl font-bold tracking-tight">
                            Application sent!
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            The club officers will review your application
                            shortly.
                        </p>

                        <dl className="mt-6 space-y-2 rounded-lg border border-border p-4 text-left text-sm">
                            <div className="flex items-center justify-between gap-4">
                                <dt className="text-muted-foreground">
                                    Name
                                </dt>
                                <dd className="text-right font-medium">
                                    {member.full_name}
                                </dd>
                            </div>
                            <div className="flex items-center justify-between gap-4">
                                <dt className="text-muted-foreground">
                                    Club
                                </dt>
                                <dd className="text-right font-medium">
                                    {member.club}
                                </dd>
                            </div>
                            <div className="flex items-center justify-between border-t border-border pt-2">
                                <dt className="text-muted-foreground">
                                    Status
                                </dt>
                                <dd className="font-semibold text-primary capitalize">
                                    {member.status}
                                </dd>
                            </div>
                        </dl>

                        <Alert className="mt-6 text-left">
                            <AlertTitle>What happens next</AlertTitle>
                            <AlertDescription>
                                The club is notified via Facebook and the
                                admin dashboard. We&apos;ve also created your
                                account — log in anytime to check your
                                application status.
                            </AlertDescription>
                        </Alert>

                        <div className="mt-6 flex flex-col gap-2 sm:flex-row sm:justify-center">
                            <Button asChild>
                                <Link href="/login">
                                    Log in to track your status
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/clubs">Back to clubs</Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/">Back to home</Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
