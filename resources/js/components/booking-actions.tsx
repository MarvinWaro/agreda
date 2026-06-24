import { router } from '@inertiajs/react';
import { Check, X } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';

export function BookingActions({ id }: { id: number }) {
    const [processing, setProcessing] = useState(false);

    const act = (verb: 'confirm' | 'decline') => {
        router.patch(
            `/admin/bookings/${id}/${verb}`,
            {},
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <div className="flex gap-1.5">
            <Button
                size="sm"
                disabled={processing}
                onClick={() => act('confirm')}
                aria-label="Confirm booking"
            >
                <Check className="size-3.5" />
                <span className="hidden sm:inline">Confirm</span>
            </Button>
            <Button
                size="sm"
                variant="outline"
                disabled={processing}
                onClick={() => act('decline')}
                aria-label="Decline booking"
            >
                <X className="size-3.5" />
                <span className="hidden sm:inline">Decline</span>
            </Button>
        </div>
    );
}
