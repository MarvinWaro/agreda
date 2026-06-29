import { Head } from '@inertiajs/react';
import { Card, CardContent } from '@/components/ui/card';

type Props = {
    page: { title: string; body: string };
};

const stats = [
    { value: '4', label: 'sports' },
    { value: '1', label: 'full court' },
    { value: '★', label: 'community run' },
];

export default function About({ page }: Props) {
    const paragraphs = page.body.split('\n\n').filter(Boolean);

    return (
        <>
            <Head title="About" />

            <div className="mx-auto w-full max-w-6xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    {page.title}
                </h1>

                <div className="mt-6 space-y-4 text-sm leading-relaxed text-muted-foreground">
                    {paragraphs.map((paragraph, index) => (
                        <p key={index}>{paragraph}</p>
                    ))}
                </div>

                <div className="mt-8 grid grid-cols-3 gap-4">
                    {stats.map((stat) => (
                        <Card key={stat.label}>
                            <CardContent className="p-5 text-center">
                                <div className="text-2xl font-bold text-primary">
                                    {stat.value}
                                </div>
                                <div className="mt-1 text-xs text-muted-foreground">
                                    {stat.label}
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>
            </div>
        </>
    );
}
