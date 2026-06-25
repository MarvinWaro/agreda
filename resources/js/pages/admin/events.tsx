import { Head, router, useForm } from '@inertiajs/react';
import { ImageOff, Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { ChangeEvent, FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type EventItem = {
    id: number;
    title: string;
    description: string | null;
    event_date: string | null;
    is_featured: boolean;
    image_url: string | null;
};

function Thumb({ url }: { url: string | null }) {
    if (!url) {
        return (
            <div className="flex h-12 w-20 items-center justify-center rounded bg-muted text-muted-foreground">
                <ImageOff className="size-4" />
            </div>
        );
    }

    return <img src={url} alt="" className="h-12 w-20 rounded object-cover" />;
}

function EventDialog({
    event,
    onClose,
}: {
    event: EventItem | null;
    onClose: () => void;
}) {
    const form = useForm<{
        title: string;
        description: string;
        event_date: string;
        is_featured: boolean;
        image: File | null;
    }>({
        title: event?.title ?? '',
        description: event?.description ?? '',
        event_date: event?.event_date ?? '',
        is_featured: event?.is_featured ?? false,
        image: null,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        const options = {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => onClose(),
        };

        if (event) {
            form.transform((data) => ({
                ...data,
                _method: 'put',
                is_featured: data.is_featured ? 1 : 0,
            }));
            form.post(`/admin/events/${event.id}`, options);
        } else {
            form.transform((data) => ({
                ...data,
                is_featured: data.is_featured ? 1 : 0,
            }));
            form.post('/admin/events', options);
        }
    };

    return (
        <Dialog open onOpenChange={(value) => !value && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {event ? 'Edit event' : 'New event'}
                    </DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="title">Title</Label>
                        <Input
                            id="title"
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
                        <Label htmlFor="description">Description</Label>
                        <textarea
                            id="description"
                            value={form.data.description}
                            onChange={(event) =>
                                form.setData('description', event.target.value)
                            }
                            rows={3}
                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="image">
                            Image{' '}
                            <span className="text-muted-foreground">
                                (JPG, PNG, WebP, GIF · max 3MB)
                            </span>
                        </Label>
                        {event?.image_url && (
                            <img
                                src={event.image_url}
                                alt=""
                                className="h-24 w-full rounded object-cover"
                            />
                        )}
                        <Input
                            id="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            onChange={(e: ChangeEvent<HTMLInputElement>) =>
                                form.setData(
                                    'image',
                                    e.target.files?.[0] ?? null,
                                )
                            }
                        />
                        {form.errors.image && (
                            <p className="text-sm text-destructive">
                                {form.errors.image}
                            </p>
                        )}
                    </div>
                    <div className="grid grid-cols-2 items-end gap-3">
                        <div className="space-y-2">
                            <Label htmlFor="event_date">Date</Label>
                            <Input
                                id="event_date"
                                type="date"
                                value={form.data.event_date}
                                onChange={(event) =>
                                    form.setData(
                                        'event_date',
                                        event.target.value,
                                    )
                                }
                            />
                        </div>
                        <label className="flex h-9 items-center gap-2 text-sm">
                            <Checkbox
                                checked={form.data.is_featured}
                                onCheckedChange={(checked) =>
                                    form.setData(
                                        'is_featured',
                                        checked === true,
                                    )
                                }
                            />
                            Featured on home
                        </label>
                    </div>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {event ? 'Save' : 'Add event'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminEvents({ events }: { events: EventItem[] }) {
    const [dialog, setDialog] = useState<{ event: EventItem | null } | null>(
        null,
    );
    const [deleteTarget, setDeleteTarget] = useState<EventItem | null>(null);

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/events/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <>
            <Head title="Admin · Events" />

            <div className="mx-auto w-full max-w-4xl space-y-6 p-4">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">
                            Events
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Featured events shown on the home page.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ event: null })}>
                        <Plus className="size-4" /> New event
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-24">
                                        Image
                                    </TableHead>
                                    <TableHead>Title</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead>Featured</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {events.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No events yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    events.map((event) => (
                                        <TableRow key={event.id}>
                                            <TableCell>
                                                <Thumb url={event.image_url} />
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {event.title}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {event.event_date ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {event.is_featured
                                                    ? 'Yes'
                                                    : 'No'}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() =>
                                                            setDialog({ event })
                                                        }
                                                        aria-label="Edit event"
                                                    >
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        className="text-muted-foreground hover:text-destructive"
                                                        onClick={() =>
                                                            setDeleteTarget(
                                                                event,
                                                            )
                                                        }
                                                        aria-label="Delete event"
                                                    >
                                                        <Trash2 className="size-4" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            {dialog && (
                <EventDialog
                    event={dialog.event}
                    onClose={() => setDialog(null)}
                />
            )}

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(value) => !value && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete event?</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        This removes it from the home page and deletes its
                        image.
                    </p>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteTarget(null)}
                        >
                            Cancel
                        </Button>
                        <Button variant="destructive" onClick={confirmDelete}>
                            Delete
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </>
    );
}
