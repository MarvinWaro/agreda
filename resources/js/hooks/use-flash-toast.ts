import { router } from '@inertiajs/react';
import { useEffect } from 'react';
import { toast } from 'sonner';
import type { FlashToast } from '@/types/ui';

type SuccessEvent = CustomEvent<{
    page: { props: { flash?: { toast?: FlashToast | null } } };
}>;

export function useFlashToast(): void {
    useEffect(() => {
        // Inertia fires `success` after every successful visit (including the
        // redirect back after a save), with the fresh page props attached.
        return router.on('success', (event) => {
            const data = (event as SuccessEvent).detail.page.props.flash?.toast;

            if (!data) {
                return;
            }

            toast[data.type](data.message, {
                description: new Date().toLocaleString(undefined, {
                    dateStyle: 'medium',
                    timeStyle: 'short',
                }),
            });
        });
    }, []);
}
