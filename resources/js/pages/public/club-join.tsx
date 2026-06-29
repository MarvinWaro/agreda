import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import PasswordInput from '@/components/password-input';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    club: {
        name: string;
        slug: string;
        description: string | null;
        membership_fee: string | null;
    };
};

export default function ClubJoin({ club }: Props) {
    const form = useForm({
        full_name: '',
        age: '',
        sex: '',
        occupation: '',
        address: '',
        phone: '',
        notes: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post(`/clubs/${club.slug}/join`);
    };

    return (
        <>
            <Head title={`Join ${club.name}`} />

            <div className="mx-auto w-full max-w-xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    Join {club.name}
                </h1>
                {club.description && (
                    <p className="mt-1 text-sm text-muted-foreground">
                        {club.description}
                    </p>
                )}
                {club.membership_fee && (
                    <p className="mt-2 text-sm font-medium text-primary">
                        Membership fee: ₱{club.membership_fee} (one-time,
                        paid in person)
                    </p>
                )}

                <Card className="mt-6">
                    <CardContent className="p-6">
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label htmlFor="full_name">Full name</Label>
                                <Input
                                    id="full_name"
                                    value={form.data.full_name}
                                    onChange={(event) =>
                                        form.setData(
                                            'full_name',
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {form.errors.full_name && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.full_name}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label htmlFor="age">Age</Label>
                                    <Input
                                        id="age"
                                        type="number"
                                        min={1}
                                        max={120}
                                        value={form.data.age}
                                        onChange={(event) =>
                                            form.setData(
                                                'age',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {form.errors.age && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.age}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="sex">Sex</Label>
                                    <Select
                                        value={form.data.sex}
                                        onValueChange={(value) =>
                                            form.setData('sex', value)
                                        }
                                    >
                                        <SelectTrigger
                                            id="sex"
                                            className="w-full"
                                        >
                                            <SelectValue placeholder="Select" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="male">
                                                Male
                                            </SelectItem>
                                            <SelectItem value="female">
                                                Female
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    {form.errors.sex && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.sex}
                                        </p>
                                    )}
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="occupation">Occupation</Label>
                                <Input
                                    id="occupation"
                                    value={form.data.occupation}
                                    onChange={(event) =>
                                        form.setData(
                                            'occupation',
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {form.errors.occupation && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.occupation}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="address">Address</Label>
                                <Input
                                    id="address"
                                    value={form.data.address}
                                    onChange={(event) =>
                                        form.setData(
                                            'address',
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {form.errors.address && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.address}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="phone">Phone</Label>
                                <Input
                                    id="phone"
                                    value={form.data.phone}
                                    onChange={(event) =>
                                        form.setData(
                                            'phone',
                                            event.target.value,
                                        )
                                    }
                                    required
                                />
                                {form.errors.phone && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.phone}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="notes">
                                    Notes (optional)
                                </Label>
                                <textarea
                                    id="notes"
                                    value={form.data.notes}
                                    onChange={(event) =>
                                        form.setData(
                                            'notes',
                                            event.target.value,
                                        )
                                    }
                                    rows={3}
                                    className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                />
                                {form.errors.notes && (
                                    <p className="text-sm text-destructive">
                                        {form.errors.notes}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-4 border-t border-border pt-4">
                                <p className="text-sm text-muted-foreground">
                                    We&apos;ll create your login so you can
                                    check your application status later.
                                </p>

                                <div className="space-y-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        value={form.data.email}
                                        onChange={(event) =>
                                            form.setData(
                                                'email',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {form.errors.email && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.email}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password">Password</Label>
                                    <PasswordInput
                                        id="password"
                                        value={form.data.password}
                                        onChange={(event) =>
                                            form.setData(
                                                'password',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {form.errors.password && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.password}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="password_confirmation">
                                        Confirm password
                                    </Label>
                                    <PasswordInput
                                        id="password_confirmation"
                                        value={
                                            form.data.password_confirmation
                                        }
                                        onChange={(event) =>
                                            form.setData(
                                                'password_confirmation',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {form.errors.password_confirmation && (
                                        <p className="text-sm text-destructive">
                                            {
                                                form.errors
                                                    .password_confirmation
                                            }
                                        </p>
                                    )}
                                </div>
                            </div>

                            <Button
                                type="submit"
                                disabled={form.processing}
                                className="w-full"
                            >
                                {form.processing ? (
                                    <>
                                        <Spinner className="size-4" />{' '}
                                        Submitting…
                                    </>
                                ) : (
                                    'Submit application'
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
