import { Head, useForm } from '@inertiajs/react';
import { Facebook, MapPin, Phone } from 'lucide-react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type Props = {
    contact: {
        phone: string | null;
        email: string | null;
        facebook_url: string | null;
        map_embed_url: string | null;
        address: string | null;
    };
};

export default function Contact({ contact }: Props) {
    const form = useForm({ name: '', message: '' });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.post('/contact', {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    return (
        <>
            <Head title="Contact" />

            <div className="mx-auto w-full max-w-5xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    Contact us
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Questions about a booking? Reach out — we&apos;re happy to
                    help.
                </p>

                <div className="mt-8 grid gap-8 md:grid-cols-2">
                    <div className="space-y-6">
                        <div className="aspect-video overflow-hidden rounded-lg border border-border bg-muted">
                            {contact.map_embed_url ? (
                                <iframe
                                    title="Map"
                                    src={contact.map_embed_url}
                                    className="size-full"
                                    loading="lazy"
                                />
                            ) : (
                                <div className="flex size-full items-center justify-center text-sm text-muted-foreground">
                                    <MapPin className="mr-2 size-4 text-primary" />
                                    Map coming soon
                                </div>
                            )}
                        </div>

                        <ul className="space-y-3 text-sm">
                            {contact.address && (
                                <li className="flex items-start gap-3">
                                    <MapPin className="mt-0.5 size-4 text-primary" />
                                    <span>{contact.address}</span>
                                </li>
                            )}
                            {contact.phone && (
                                <li className="flex items-center gap-3">
                                    <Phone className="size-4 text-primary" />
                                    <a
                                        href={`tel:${contact.phone}`}
                                        className="transition-colors hover:text-primary"
                                    >
                                        {contact.phone}
                                    </a>
                                </li>
                            )}
                            {contact.facebook_url && (
                                <li className="flex items-center gap-3">
                                    <Facebook className="size-4 text-primary" />
                                    <a
                                        href={contact.facebook_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="transition-colors hover:text-primary"
                                    >
                                        Message us on Facebook
                                    </a>
                                </li>
                            )}
                        </ul>
                    </div>

                    <Card>
                        <CardContent className="p-6">
                            <h2 className="text-base font-semibold">
                                Send a message
                            </h2>
                            <form onSubmit={submit} className="mt-4 space-y-4">
                                <div className="space-y-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        value={form.data.name}
                                        onChange={(event) =>
                                            form.setData(
                                                'name',
                                                event.target.value,
                                            )
                                        }
                                        required
                                    />
                                    {form.errors.name && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.name}
                                        </p>
                                    )}
                                </div>
                                <div className="space-y-2">
                                    <Label htmlFor="message">Message</Label>
                                    <textarea
                                        id="message"
                                        value={form.data.message}
                                        onChange={(event) =>
                                            form.setData(
                                                'message',
                                                event.target.value,
                                            )
                                        }
                                        rows={4}
                                        required
                                        className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    />
                                    {form.errors.message && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.message}
                                        </p>
                                    )}
                                </div>
                                <Button
                                    type="submit"
                                    disabled={form.processing}
                                    className="w-full"
                                >
                                    {form.processing ? (
                                        <>
                                            <Spinner className="size-4" />{' '}
                                            Sending…
                                        </>
                                    ) : (
                                        'Send message'
                                    )}
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
