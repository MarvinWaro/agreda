import { Head, Link, router } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Carousel,
    CarouselContent,
    CarouselItem,
    CarouselNext,
    CarouselPrevious,
} from '@/components/ui/carousel';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';

type Slide = {
    id: number;
    title: string;
    caption: string | null;
    image_path: string | null;
    link_url: string | null;
};

type Event = {
    id: number;
    title: string;
    description: string | null;
    date: string | null;
    image_path: string | null;
};

type Sport = { id: number; name: string; slug: string };

type Props = {
    slides: Slide[];
    events: Event[];
    sports: Sport[];
};

export default function Home({ slides, events, sports }: Props) {
    const [sport, setSport] = useState<string>('');
    const [date, setDate] = useState<string>('');

    const heroSlides: Slide[] =
        slides.length > 0
            ? slides
            : [
                  {
                      id: 0,
                      title: 'Book the court at AGREDA PHASE III',
                      caption: 'One court, four sports',
                      image_path: null,
                      link_url: null,
                  },
              ];

    return (
        <>
            <Head title="Home" />

            {/* Hero carousel */}
            <section className="mx-auto w-full max-w-6xl px-4 pt-8 sm:px-6">
                <Carousel className="w-full">
                    <CarouselContent>
                        {heroSlides.map((slide) => (
                            <CarouselItem key={slide.id}>
                                <div className="relative flex h-72 items-center justify-center overflow-hidden rounded-xl bg-gradient-to-br from-primary/15 via-primary/5 to-background sm:h-80">
                                    {slide.image_path && (
                                        <img
                                            src={slide.image_path}
                                            alt=""
                                            className="absolute inset-0 size-full object-cover"
                                        />
                                    )}
                                    <div className="relative max-w-xl px-6 text-center">
                                        <h1 className="text-2xl font-bold tracking-tight sm:text-3xl">
                                            {slide.title}
                                        </h1>
                                        {slide.caption && (
                                            <p className="mt-2 text-sm text-muted-foreground">
                                                {slide.caption}
                                            </p>
                                        )}
                                        <Button asChild className="mt-5">
                                            <Link href="/book">Book now →</Link>
                                        </Button>
                                    </div>
                                </div>
                            </CarouselItem>
                        ))}
                    </CarouselContent>
                    {heroSlides.length > 1 && (
                        <>
                            <CarouselPrevious className="left-3" />
                            <CarouselNext className="right-3" />
                        </>
                    )}
                </Carousel>
            </section>

            {/* Quick check availability */}
            <section className="mx-auto w-full max-w-6xl px-4 pt-6 sm:px-6">
                <Card>
                    <CardContent className="p-5">
                        <div className="mb-3 flex items-center gap-2 text-sm font-medium">
                            <CalendarDays className="size-4 text-primary" />
                            Quick check availability
                        </div>
                        <div className="grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                            <Select value={sport} onValueChange={setSport}>
                                <SelectTrigger>
                                    <SelectValue placeholder="Sport" />
                                </SelectTrigger>
                                <SelectContent>
                                    {sports.map((item) => (
                                        <SelectItem
                                            key={item.id}
                                            value={item.slug}
                                        >
                                            {item.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <input
                                type="date"
                                value={date}
                                onChange={(event) =>
                                    setDate(event.target.value)
                                }
                                className="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-xs outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            />
                            <Button onClick={() => router.visit('/book')}>
                                Check
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </section>

            {/* Sport chips */}
            <section className="mx-auto w-full max-w-6xl px-4 pt-8 sm:px-6">
                <h2 className="text-sm font-semibold text-muted-foreground">
                    One court — four sports
                </h2>
                <div className="mt-3 flex flex-wrap gap-2">
                    {sports.map((item) => (
                        <Link
                            key={item.id}
                            href="/book"
                            className="rounded-full border border-border px-4 py-1.5 text-sm font-medium transition-colors hover:border-primary hover:text-primary"
                        >
                            {item.name}
                        </Link>
                    ))}
                </div>
            </section>

            {/* Featured events */}
            {events.length > 0 && (
                <section className="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6">
                    <h2 className="text-lg font-bold tracking-tight">
                        Featured events
                    </h2>
                    <div className="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {events.map((event) => (
                            <Card key={event.id} className="overflow-hidden">
                                <div className="h-32 bg-gradient-to-br from-primary/15 to-muted" />
                                <CardContent className="p-4">
                                    {event.date && (
                                        <div className="text-xs font-medium text-primary">
                                            {event.date}
                                        </div>
                                    )}
                                    <div className="mt-1 font-semibold">
                                        {event.title}
                                    </div>
                                    {event.description && (
                                        <p className="mt-1 text-sm text-muted-foreground">
                                            {event.description}
                                        </p>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </section>
            )}
        </>
    );
}
