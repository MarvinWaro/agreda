import { Head, router, useForm } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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

type Faq = {
    id: number;
    category: string | null;
    question: string;
    answer: string;
    sort_order: number;
};

function FaqDialog({ faq, onClose }: { faq: Faq | null; onClose: () => void }) {
    const form = useForm({
        question: faq?.question ?? '',
        answer: faq?.answer ?? '',
        category: faq?.category ?? '',
        sort_order: faq?.sort_order ?? 0,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const options = { preserveScroll: true, onSuccess: () => onClose() };

        if (faq) {
            form.put(`/admin/faqs/${faq.id}`, options);
        } else {
            form.post('/admin/faqs', options);
        }
    };

    return (
        <Dialog open onOpenChange={(value) => !value && onClose()}>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{faq ? 'Edit FAQ' : 'New FAQ'}</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="question">Question</Label>
                        <Input
                            id="question"
                            value={form.data.question}
                            onChange={(event) =>
                                form.setData('question', event.target.value)
                            }
                            required
                        />
                        {form.errors.question && (
                            <p className="text-sm text-destructive">
                                {form.errors.question}
                            </p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label htmlFor="answer">Answer</Label>
                        <textarea
                            id="answer"
                            value={form.data.answer}
                            onChange={(event) =>
                                form.setData('answer', event.target.value)
                            }
                            rows={4}
                            required
                            className="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        />
                        {form.errors.answer && (
                            <p className="text-sm text-destructive">
                                {form.errors.answer}
                            </p>
                        )}
                    </div>
                    <div className="grid grid-cols-2 gap-3">
                        <div className="space-y-2">
                            <Label htmlFor="category">Category</Label>
                            <Input
                                id="category"
                                value={form.data.category}
                                onChange={(event) =>
                                    form.setData('category', event.target.value)
                                }
                                placeholder="e.g. Booking"
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
                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                        >
                            Cancel
                        </Button>
                        <Button type="submit" disabled={form.processing}>
                            {faq ? 'Save' : 'Add FAQ'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}

export default function AdminFaqs({ faqs }: { faqs: Faq[] }) {
    const [dialog, setDialog] = useState<{ faq: Faq | null } | null>(null);
    const [deleteTarget, setDeleteTarget] = useState<Faq | null>(null);

    const confirmDelete = () => {
        if (!deleteTarget) {
            return;
        }

        router.delete(`/admin/faqs/${deleteTarget.id}`, {
            preserveScroll: true,
            onFinish: () => setDeleteTarget(null),
        });
    };

    return (
        <>
            <Head title="Admin · FAQs" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-xl font-bold tracking-tight">
                            FAQs
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            Questions shown on the public Rules &amp; FAQ page.
                        </p>
                    </div>
                    <Button onClick={() => setDialog({ faq: null })}>
                        <Plus className="size-4" /> New FAQ
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-16">
                                        Order
                                    </TableHead>
                                    <TableHead>Question</TableHead>
                                    <TableHead>Category</TableHead>
                                    <TableHead className="text-right">
                                        Actions
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {faqs.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-10 text-center text-muted-foreground"
                                        >
                                            No FAQs yet.
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    faqs.map((faq) => (
                                        <TableRow key={faq.id}>
                                            <TableCell className="text-muted-foreground">
                                                {faq.sort_order}
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {faq.question}
                                            </TableCell>
                                            <TableCell className="text-muted-foreground">
                                                {faq.category ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        onClick={() =>
                                                            setDialog({ faq })
                                                        }
                                                        aria-label="Edit FAQ"
                                                    >
                                                        <Pencil className="size-4" />
                                                    </Button>
                                                    <Button
                                                        size="sm"
                                                        variant="ghost"
                                                        className="text-muted-foreground hover:text-destructive"
                                                        onClick={() =>
                                                            setDeleteTarget(faq)
                                                        }
                                                        aria-label="Delete FAQ"
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
                <FaqDialog faq={dialog.faq} onClose={() => setDialog(null)} />
            )}

            <Dialog
                open={deleteTarget !== null}
                onOpenChange={(value) => !value && setDeleteTarget(null)}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete FAQ?</DialogTitle>
                    </DialogHeader>
                    <p className="text-sm text-muted-foreground">
                        This removes it from the public FAQ page.
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
