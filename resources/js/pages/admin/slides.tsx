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

type Slide = {
    id: number;
    title: string;
    caption: string | null;
    link_url: string | null;
    sort_order: number;
    is_visible: boolean;
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

function SlideDialog({
    slide,
    onClose,
}: {
    slide: Slide | null;
    onClose: () => void;
}) {
    const form = useForm<{
        title: string;
        caption: string;
        link_url: string;
        sort_order: number;
        is_visible: boolean;
        image: File | null;
    }>({
        title: slide?.title ?? '',
        caption: slide?.caption ?? '',
        link_url: slide?.link_url ?? '',
        sort_order: slide?.sort_order ?? 0,
        is_visible: slide?.is_visible ?? true,
        image: null,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => onClose(),
        };

        if (slide) {
            form.transform((data) => ({
                ...data,
                _method: 'put',
                is_visible: data.is_visible ? 1 : 0,
            }));
            form.post(`/admin/slides/${slide.id}`, options);
        } else {
            form.transform((data) => ({
                ...data,
                is_visible: data.is_visible ? 1 : 0,
            }));
            form.post('/admin/slides', options);
        }
    };

    return (
        <Dialog open onOpenChange={(value) => !value && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {slide ? 'Edit slide' : 'New slide'}
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
                        <Label htmlFor="image">
                            Image{' '}
                            <span className="text-muted-foreground">
                                (JPG, PNG, WebP, GIF · max 3MB)
                            </span>
                        </Label>
                        {slide?.image_url && (
                            <img
                                src={slide.image_url}
                                alt=""
                                className="h-24 w-full rounded object-cover"
                            />
                        )}
                        <Input
                            id="image"
                            type="file"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            onChange={(event: ChangeEvent<HTMLInputElement>) =>
                                form.setData(
                                    'image',
                                    event.target.files?.[0] ?? null,
                                )
                            }
                        />
                        {form.errors.image && (
                            <p className="text-sm text-destructive">
                                {form.errors.image}
                            </p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="caption">Caption</Label>
                        <Input
                            id="caption"
                            value={form.data.caption}
                            onChange={(event) =>
                                form.setData('caption', event.target.value)
                            }
                        />
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-2">
                            <Label htmlFor="link_url">Link URL</Label>
                            <Input
                                id="link_url"
                                value={form.data.link_url}
                                onChange={(event) =>
                                    form.setData('link_url', event.target.value)
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="sort_order">Sort order</Label>
                            <Input
                                id="sort_order"
                                type="number"
                                min="0"
                                value={form.data.sort_order}
                                onChange={(event) =>
                                    form.setData(
                                        'sort_order',
                                        Number(event.target.value),
                                    )
                                }
                            />
                        </div>
                    </div>
                    <label className="flex items-center gap-2 text-sm">
                        <Checkbox
                            checked={form.data.is_visible}
                            onCheckedChange={(checked) =>
                                form.setData('is_visible', checked === true)
                            }
                        />
                        Visible on the site
                    </label>
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {slide ? 'Save' : 'Add slide'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminSlides({ slides }: { slides: Slide[] }) {
    const [dialog, setDialog] = useState<{ slide: Slide | null } | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Slide | null>(null);

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/slides/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <>
            <Head title="Admin · Carousel" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">
                            Carousel slides
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Images shown in the home page hero carousel.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ slide: null })}>
                        <Plus className="size-4" /> New slide
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
                                    <TableHead>Visible</TableHead>
                                    <TableHead className="w-16">
                                        Order
                                    </TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {slides.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No slides yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    slides.map((slide) => (
                                        <TableRow key={slide.id}>
                                            <TableCell>
                                                <Thumb url={slide.image_url} />
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {slide.title}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {slide.is_visible
                                                    ? 'Visible'
                                                    : 'Hidden'}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {slide.sort_order}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() =>
                                                            setDialog({ slide })
                                                        }
                                                        aria-label="Edit slide"
                                                    >
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        className="text-muted-foreground hover:text-destructive"
                                                        onClick={() =>
                                                            setDeleteTarget(
                                                                slide,
                                                            )
                                                        }
                                                        aria-label="Delete slide"
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
                <SlideDialog
                    slide={dialog.slide}
                    onClose={() => setDialog(null)}
                />
            )}

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(value) => !value && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete slide?</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        This removes it from the carousel and deletes its image.
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
