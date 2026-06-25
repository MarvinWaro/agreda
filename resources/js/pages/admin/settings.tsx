import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';

type SettingsForm = {
    contact_phone: string;
    contact_email: string;
    facebook_url: string;
    address: string;
    opening_hours: string;
    map_embed_url: string;
};

type Props = {
    settings: Record<keyof SettingsForm, string | null>;
};

type Field = {
    key: keyof SettingsForm;
    label: string;
    type?: string;
    multiline?: boolean;
    help?: string;
};

const fields: Field[] = [
    { key: 'contact_phone', label: 'Phone' },
    { key: 'contact_email', label: 'Email', type: 'email' },
    { key: 'facebook_url', label: 'Facebook URL', type: 'url' },
    { key: 'address', label: 'Address' },
    { key: 'opening_hours', label: 'Opening hours' },
    {
        key: 'map_embed_url',
        label: 'Google Maps embed',
        multiline: true,
        help: 'In Google Maps: Share → "Embed a map" → Copy HTML, then paste it here (or just the https://www.google.com/maps/embed?… URL). A normal share link won\'t work.',
    },
];

export default function AdminSettings({ settings }: Props) {
    const form = useForm<SettingsForm>({
        contact_phone: settings.contact_phone ?? '',
        contact_email: settings.contact_email ?? '',
        facebook_url: settings.facebook_url ?? '',
        address: settings.address ?? '',
        opening_hours: settings.opening_hours ?? '',
        map_embed_url: settings.map_embed_url ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put('/admin/settings', { preserveScroll: true });
    };

    return (
        <>
            <Head title="Admin · Settings" />

            <div className="max-w-2xl space-y-6">
                <div>
                    <h1 className="text-xl font-bold tracking-tight">
                        Settings
                    </h1>
                    <p className="text-sm text-muted-foreground">
                        Contact details shown on the public site and footer.
                    </p>
                </div>

                <Card>
                    <CardContent className="p-6">
                        <form onSubmit={submit} className="space-y-4">
                            {fields.map((field) => (
                                <div key={field.key} className="space-y-2">
                                    <Label htmlFor={field.key}>
                                        {field.label}
                                    </Label>
                                    {field.multiline ? (
                                        <textarea
                                            id={field.key}
                                            rows={3}
                                            value={form.data[field.key]}
                                            onChange={(event) =>
                                                form.setData(
                                                    field.key,
                                                    event.target.value,
                                                )
                                            }
                                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        />
                                    ) : (
                                        <Input
                                            id={field.key}
                                            type={field.type ?? 'text'}
                                            value={form.data[field.key]}
                                            onChange={(event) =>
                                                form.setData(
                                                    field.key,
                                                    event.target.value,
                                                )
                                            }
                                        />
                                    )}
                                    {field.help && (
                                        <p className="text-xs text-muted-foreground">
                                            {field.help}
                                        </p>
                                    )}
                                    {form.errors[field.key] && (
                                        <p className="text-sm text-destructive">
                                            {form.errors[field.key]}
                                        </p>
                                    )}
                                </div>
                            ))}

                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? (
                                    <>
                                        <Spinner className="size-4" /> Saving…
                                    </>
                                ) : (
                                    'Save settings'
                                )}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
