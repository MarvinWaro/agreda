import { Head } from '@inertiajs/react';
import {
    Accordion,
    AccordionContent,
    AccordionItem,
    AccordionTrigger,
} from '@/components/ui/accordion';

type Faq = {
    id: number;
    category: string | null;
    question: string;
    answer: string;
};

export default function Faqs({ faqs }: { faqs: Faq[] }) {
    return (
        <>
            <Head title="Rules & FAQ" />

            <div className="mx-auto w-full max-w-3xl px-4 py-12 sm:px-6">
                <h1 className="text-2xl font-bold tracking-tight">
                    Rules &amp; FAQ
                </h1>
                <p className="mt-1 text-sm text-muted-foreground">
                    Everything you need to know about booking and playing.
                </p>

                {faqs.length === 0 ? (
                    <p className="mt-6 text-sm text-muted-foreground">
                        No questions yet.
                    </p>
                ) : (
                    <Accordion type="single" collapsible className="mt-6">
                        {faqs.map((faq) => (
                            <AccordionItem key={faq.id} value={`faq-${faq.id}`}>
                                <AccordionTrigger className="text-left">
                                    {faq.question}
                                </AccordionTrigger>
                                <AccordionContent className="text-muted-foreground">
                                    {faq.answer}
                                </AccordionContent>
                            </AccordionItem>
                        ))}
                    </Accordion>
                )}
            </div>
        </>
    );
}
