import { Head, useForm } from '@inertiajs/react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

type Page = {
    id: number;
    slug: string;
    title: string;
    body: string | null;
};

function PageEditor({ page }: { page: Page }) {
    const form = useForm({
        title: page.title,
        body: page.body ?? '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        form.put(`/admin/pages/${page.id}`, { preserveScroll: true });
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-base">
                    <span className="font-mono text-xs text-muted-foreground">
                        /{page.slug}
                    </span>
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor={`title-${page.id}`}>Title</Label>
                        <Input
                            id={`title-${page.id}`}
                            value={form.data.title}
                            onChange={(event) =>
                                form.setData('title', event.target.value)
                            }
                            required
                        />
                        {form.errors.title && (
                            <p className="text-sm text-destructive">
                                {form.errors.title}
                            </p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor={`body-${page.id}`}>Body</Label>
                        <textarea
                            id={`body-${page.id}`}
                            value={form.data.body}
                            onChange={(event) =>
                                form.setData('body', event.target.value)
                            }
                            rows={6}
                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        <p className="text-xs text-muted-foreground">
                            Separate paragraphs with a blank line.
                        </p>
                    </div>
                    <Button
                        type="submit"
                        disabled={form.processing || !form.isDirty}
                    >
                        Save
                    </Button>
                </form>
            </CardContent>
        </Card>
    );
}

export default function AdminPages({ pages }: { pages: Page[] }) {
    return (
        <>
            <Head title="Admin · Pages" />

            <div className="max-w-2xl space-y-6">
                <div>
                    <h1 className="text-xl font-bold tracking-tight">Pages</h1>
                    <p className="text-sm text-muted-foreground">
                        Edit the About and Facilities page content.
                    </p>
                </div>

                <div className="space-y-4">
                    {pages.map((page) => (
                        <PageEditor key={page.id} page={page} />
                    ))}
                </div>
            </div>
        </>
    );
}
