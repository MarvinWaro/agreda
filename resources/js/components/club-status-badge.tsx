import { cn } from '@/lib/utils';

const styles: Record<string, string> = {
    pending:
        'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300',
    approved:
        'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300',
    declined: 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
};

const labels: Record<string, string> = {
    pending: 'Pending',
    approved: 'Approved',
    declined: 'Declined',
};

export function ClubStatusBadge({ status }: { status: string }) {
    return (
        <span
            className={cn(
                'inline-flex rounded-full px-2 py-0.5 text-xs font-medium',
                styles[status] ?? 'bg-muted text-muted-foreground',
            )}
        >
            {labels[status] ?? status}
        </span>
    );
}
