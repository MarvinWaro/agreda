import { Head, useForm } from '@inertiajs/react';
import { CalendarDays, Check, Minus, Plus } from 'lucide-react';
import { useRef, useState } from 'react';
import type { FormEvent } from 'react';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { ToggleGroup, ToggleGroupItem } from '@/components/ui/toggle-group';
import { cn } from '@/lib/utils';

type SportOption = {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    rate_offpeak: string;
    rate_peak: string;
};

type Slot = {
    start: string;
    end: string;
    label: string;
    status: 'free' | 'pending' | 'booked' | 'past' | 'closed';
    selectable: boolean;
};

type Availability = {
    date: string;
    closed: boolean;
    closed_reason: string | null;
    slots: Slot[];
};

type BookForm = {
    sport_id: number | null;
    date: string;
    start_time: string;
    end_time: string;
    guest_name: string;
    guest_phone: string;
    notes: string;
};

type Props = {
    sports: SportOption[];
    court: { id: number; name: string; location: string | null } | null;
};

const stepLabels = ['Sport', 'Date', 'Time', 'Details'];

function toYmd(date: Date): string {
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
}

/** Format a "HH:MM" 24h string as a 12h label, e.g. "11:00 AM". */
function formatTime(hhmm: string): string {
    const [hour, minute] = hhmm.split(':').map(Number);
    const date = new Date();
    date.setHours(hour, minute, 0, 0);

    return date.toLocaleTimeString(undefined, {
        hour: 'numeric',
        minute: '2-digit',
    });
}

const slotMeta: Record<Slot['status'], { label: string; className: string }> = {
    free: { label: 'Free', className: 'text-emerald-600' },
    pending: { label: 'Pending', className: 'text-amber-600' },
    booked: { label: 'Booked', className: 'text-muted-foreground' },
    past: { label: 'Past', className: 'text-muted-foreground' },
    closed: { label: 'Closed', className: 'text-muted-foreground' },
};

function Stepper({ current }: { current: number }) {
    return (
        <ol className="flex flex-wrap items-center gap-2 text-xs">
            {stepLabels.map((label, index) => {
                const done = index < current;
                const active = index === current;

                return (
                    <li key={label} className="flex items-center gap-2">
                        <span
                            className={cn(
                                'flex items-center gap-1.5 rounded-full px-2.5 py-1 font-medium',
                                active && 'bg-primary text-primary-foreground',
                                done && 'bg-primary/10 text-primary',
                                !active &&
                                    !done &&
                                    'bg-muted text-muted-foreground',
                            )}
                        >
                            <span>{index + 1}</span>
                            {label}
                        </span>
                        {index < stepLabels.length - 1 && (
                            <span className="text-muted-foreground">›</span>
                        )}
                    </li>
                );
            })}
        </ol>
    );
}

export default function Book({ sports }: Props) {
    const [sportId, setSportId] = useState<number | null>(null);
    const [date, setDate] = useState<Date | undefined>(undefined);
    // A booking is a start slot plus a duration in hours, so the end time is
    // always explicit instead of inferred from which slot was clicked last.
    const [startIdx, setStartIdx] = useState<number | null>(null);
    const [hours, setHours] = useState(1);
    const [availability, setAvailability] = useState<Availability | null>(null);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [slotsError, setSlotsError] = useState<string | null>(null);

    const form = useForm<BookForm>({
        sport_id: null,
        date: '',
        start_time: '',
        end_time: '',
        guest_name: '',
        guest_phone: '',
        notes: '',
    });

    const dateStr = date ? toYmd(date) : null;
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const requestRef = useRef(0);

    const slots = availability?.slots ?? [];

    const resetSelection = () => {
        setStartIdx(null);
        setHours(1);
    };

    // How many contiguous, free hour-slots run from `start` onward.
    const maxHoursFrom = (start: number): number => {
        let count = 0;

        for (let i = start; i < slots.length; i++) {
            if (!slots[i].selectable) {
                break;
            }

            if (i > start && slots[i - 1].end !== slots[i].start) {
                break;
            }

            count++;
        }

        return count;
    };

    const maxHours = startIdx !== null ? maxHoursFrom(startIdx) : 0;
    const rangeChosen = startIdx !== null;
    const endIdx =
        startIdx !== null
            ? startIdx + Math.min(hours, Math.max(maxHours, 1)) - 1
            : null;

    const handleSlotClick = (index: number) => {
        if (!slots[index].selectable) {
            return;
        }

        setStartIdx(index);
        setHours(1);
    };

    const adjustHours = (delta: number) => {
        setHours((current) =>
            Math.min(Math.max(current + delta, 1), Math.max(maxHours, 1)),
        );
    };

    const loadSlots = (
        nextSportId: number | null,
        nextDate: Date | undefined,
    ) => {
        resetSelection();
        setAvailability(null);
        setSlotsError(null);

        if (!nextSportId || !nextDate) {
            return;
        }

        const requestId = ++requestRef.current;
        setLoadingSlots(true);

        fetch(
            `/api/availability?sport_id=${nextSportId}&date=${toYmd(nextDate)}`,
            { headers: { Accept: 'application/json' } },
        )
            .then((response) => {
                if (!response.ok) {
                    throw new Error('Request failed');
                }

                return response.json();
            })
            .then((data: Availability) => {
                if (requestRef.current === requestId) {
                    setAvailability(data);
                }
            })
            .catch(() => {
                if (requestRef.current === requestId) {
                    setSlotsError(
                        'Could not load availability. Please try again.',
                    );
                }
            })
            .finally(() => {
                if (requestRef.current === requestId) {
                    setLoadingSlots(false);
                }
            });
    };

    const handleSportChange = (value: string) => {
        const nextSportId = value ? Number(value) : null;
        setSportId(nextSportId);
        loadSlots(nextSportId, date);
    };

    const handleDateChange = (nextDate: Date | undefined) => {
        setDate(nextDate);
        loadSlots(sportId, nextDate);
    };

    const currentStep = !sportId ? 0 : !date ? 1 : !rangeChosen ? 2 : 3;
    const selectedSport = sports.find((sport) => sport.id === sportId) ?? null;

    const selectedStart =
        startIdx !== null && slots[startIdx] ? slots[startIdx].start : null;
    const selectedEnd =
        endIdx !== null && slots[endIdx] ? slots[endIdx].end : null;
    const selectedHours =
        startIdx !== null && endIdx !== null ? endIdx - startIdx + 1 : 0;

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (!sportId || !dateStr || !selectedStart || !selectedEnd) {
            return;
        }

        form.transform((data) => ({
            ...data,
            sport_id: sportId,
            date: dateStr,
            start_time: selectedStart,
            end_time: selectedEnd,
        }));

        form.post('/book', { preserveScroll: true });
    };

    return (
        <>
            <Head title="Book the court" />

            <div className="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6">
                <div className="mb-6">
                    <h1 className="text-2xl font-bold tracking-tight">
                        Book the court
                    </h1>
                    <p className="mt-1 text-sm text-muted-foreground">
                        Pick a sport, date and time slot, then send your
                        request. The owner confirms availability before
                        it&apos;s final.
                    </p>
                </div>

                <div className="mb-8">
                    <Stepper current={currentStep} />
                </div>

                <div className="space-y-6">
                    {/* Step 1 — sport */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                1 · Choose a sport
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ToggleGroup
                                type="single"
                                value={sportId ? String(sportId) : ''}
                                onValueChange={handleSportChange}
                                className="flex flex-wrap justify-start gap-2"
                            >
                                {sports.map((sport) => (
                                    <ToggleGroupItem
                                        key={sport.id}
                                        value={String(sport.id)}
                                        variant="outline"
                                        className="h-10 rounded-full px-4 data-[state=on]:border-primary data-[state=on]:bg-primary data-[state=on]:text-primary-foreground"
                                    >
                                        {sport.name}
                                    </ToggleGroupItem>
                                ))}
                            </ToggleGroup>
                            {selectedSport && (
                                <p className="mt-3 text-xs text-muted-foreground">
                                    Rates: ₱{selectedSport.rate_offpeak}{' '}
                                    off-peak · ₱{selectedSport.rate_peak} peak
                                    (evenings &amp; weekends). Final total is
                                    shown after you submit.
                                </p>
                            )}
                            {form.errors.sport_id && (
                                <p className="mt-2 text-sm text-destructive">
                                    {form.errors.sport_id}
                                </p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Step 2 + 3 — date & slots */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                2 · Pick a date &amp; time
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-6 sm:grid-cols-[auto_1fr]">
                            <Calendar
                                mode="single"
                                selected={date}
                                onSelect={handleDateChange}
                                disabled={{ before: today }}
                                className="rounded-md border"
                            />

                            <div className="min-w-0">
                                <div className="mb-2 flex items-center gap-2 text-sm font-medium">
                                    <CalendarDays className="size-4 text-primary" />
                                    {date
                                        ? date.toLocaleDateString(undefined, {
                                              month: 'short',
                                              day: 'numeric',
                                              year: 'numeric',
                                          })
                                        : 'Select a date'}
                                </div>

                                {!sportId && (
                                    <p className="text-sm text-muted-foreground">
                                        Choose a sport first.
                                    </p>
                                )}

                                {sportId && !date && (
                                    <p className="text-sm text-muted-foreground">
                                        Choose a date to see open slots.
                                    </p>
                                )}

                                {loadingSlots && (
                                    <div className="flex items-center gap-2 text-sm text-muted-foreground">
                                        <Spinner className="size-4" /> Loading
                                        slots…
                                    </div>
                                )}

                                {slotsError && (
                                    <p className="text-sm text-destructive">
                                        {slotsError}
                                    </p>
                                )}

                                {availability?.closed && (
                                    <p className="text-sm text-muted-foreground">
                                        {availability.closed_reason ??
                                            'Closed on this day.'}
                                    </p>
                                )}

                                {availability &&
                                    !availability.closed &&
                                    availability.slots.length > 0 && (
                                        <>
                                            <p className="mb-2 text-xs text-muted-foreground">
                                                {startIdx === null
                                                    ? 'Tap a start time, then set how many hours you need.'
                                                    : `${formatTime(selectedStart!)} – ${formatTime(selectedEnd!)} · ${selectedHours} ${selectedHours === 1 ? 'hour' : 'hours'} selected.`}
                                            </p>
                                            <div className="grid grid-cols-4 gap-1.5 sm:grid-cols-6 md:grid-cols-8">
                                                {availability.slots.map(
                                                    (item, index) => {
                                                        const isInRange =
                                                            startIdx !== null &&
                                                            index >= startIdx &&
                                                            index <=
                                                                (endIdx ??
                                                                    startIdx);

                                                        return (
                                                            <button
                                                                key={item.start}
                                                                type="button"
                                                                title={`${formatTime(item.start)} – ${formatTime(item.end)} · ${slotMeta[item.status].label}`}
                                                                disabled={
                                                                    !item.selectable
                                                                }
                                                                onClick={() =>
                                                                    handleSlotClick(
                                                                        index,
                                                                    )
                                                                }
                                                                className={cn(
                                                                    'rounded-md border px-2 py-1.5 text-xs font-medium transition-colors',
                                                                    isInRange
                                                                        ? 'border-primary bg-primary text-primary-foreground'
                                                                        : item.status ===
                                                                            'pending'
                                                                          ? 'cursor-not-allowed border-amber-200 bg-amber-50 text-amber-700'
                                                                          : item.selectable
                                                                            ? 'border-input hover:border-primary hover:bg-primary/5'
                                                                            : 'cursor-not-allowed border-transparent bg-muted text-muted-foreground',
                                                                )}
                                                            >
                                                                {formatTime(
                                                                    item.start,
                                                                )}
                                                            </button>
                                                        );
                                                    },
                                                )}
                                            </div>
                                            <div className="mt-3 flex flex-wrap items-center gap-3 text-[11px] text-muted-foreground">
                                                <span className="flex items-center gap-1">
                                                    <span className="size-2 rounded-full bg-primary" />{' '}
                                                    Selected
                                                </span>
                                                <span className="flex items-center gap-1">
                                                    <span className="size-2 rounded-full border border-input" />{' '}
                                                    Free
                                                </span>
                                                <span className="flex items-center gap-1">
                                                    <span className="size-2 rounded-full bg-amber-200" />{' '}
                                                    Pending
                                                </span>
                                                <span className="flex items-center gap-1">
                                                    <span className="size-2 rounded-full bg-muted-foreground/40" />{' '}
                                                    Booked / past
                                                </span>
                                            </div>

                                            {startIdx !== null && (
                                                <div className="mt-4 flex items-center gap-3">
                                                    <span className="text-xs font-medium">
                                                        Duration
                                                    </span>
                                                    <div className="flex items-center gap-2">
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="icon"
                                                            className="size-7"
                                                            onClick={() =>
                                                                adjustHours(-1)
                                                            }
                                                            disabled={
                                                                hours <= 1
                                                            }
                                                        >
                                                            <Minus className="size-3.5" />
                                                        </Button>
                                                        <span className="w-20 text-center text-sm font-medium">
                                                            {hours}{' '}
                                                            {hours === 1
                                                                ? 'hour'
                                                                : 'hours'}
                                                        </span>
                                                        <Button
                                                            type="button"
                                                            variant="outline"
                                                            size="icon"
                                                            className="size-7"
                                                            onClick={() =>
                                                                adjustHours(1)
                                                            }
                                                            disabled={
                                                                hours >=
                                                                maxHours
                                                            }
                                                        >
                                                            <Plus className="size-3.5" />
                                                        </Button>
                                                    </div>
                                                </div>
                                            )}
                                        </>
                                    )}

                                {form.errors.date && (
                                    <p className="mt-2 text-sm text-destructive">
                                        {form.errors.date}
                                    </p>
                                )}
                                {form.errors.start_time && (
                                    <p className="mt-2 text-sm text-destructive">
                                        {form.errors.start_time}
                                    </p>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    {/* Step 4 — details */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">
                                3 · Your details
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <form
                                onSubmit={submit}
                                className="mx-auto max-w-xl space-y-4"
                            >
                                <div className="space-y-2">
                                    <Label htmlFor="guest_name">
                                        Full name
                                    </Label>
                                    <Input
                                        id="guest_name"
                                        value={form.data.guest_name}
                                        onChange={(event) =>
                                            form.setData(
                                                'guest_name',
                                                event.target.value,
                                            )
                                        }
                                        autoComplete="name"
                                        required
                                    />
                                    {form.errors.guest_name && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.guest_name}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="guest_phone">
                                        Phone / Facebook
                                    </Label>
                                    <Input
                                        id="guest_phone"
                                        value={form.data.guest_phone}
                                        onChange={(event) =>
                                            form.setData(
                                                'guest_phone',
                                                event.target.value,
                                            )
                                        }
                                        autoComplete="tel"
                                        required
                                    />
                                    {form.errors.guest_phone && (
                                        <p className="text-sm text-destructive">
                                            {form.errors.guest_phone}
                                        </p>
                                    )}
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="notes">
                                        Notes{' '}
                                        <span className="text-muted-foreground">
                                            (optional)
                                        </span>
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
                                </div>

                                <div className="rounded-md bg-muted/60 p-3 text-xs text-muted-foreground">
                                    💵 Payment is made in person at the court.
                                    The owner confirms your request before
                                    it&apos;s final.
                                </div>

                                <Button
                                    type="submit"
                                    className="w-full"
                                    disabled={!rangeChosen || form.processing}
                                >
                                    {form.processing ? (
                                        <>
                                            <Spinner className="size-4" />{' '}
                                            Sending…
                                        </>
                                    ) : (
                                        <>
                                            <Check className="size-4" /> Submit
                                            request
                                        </>
                                    )}
                                </Button>

                                {!rangeChosen && (
                                    <p className="text-center text-xs text-muted-foreground">
                                        Select a sport, date and time range to
                                        continue.
                                    </p>
                                )}
                            </form>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}
