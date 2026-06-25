import type { LucideIcon } from 'lucide-react';
import { Check, Monitor, Moon, Sun } from 'lucide-react';
import type { Appearance } from '@/hooks/use-appearance';
import { useAppearance } from '@/hooks/use-appearance';
import { cn } from '@/lib/utils';

type Option = {
    value: Appearance;
    icon: LucideIcon;
    title: string;
    description: string;
};

const options: Option[] = [
    {
        value: 'light',
        icon: Sun,
        title: 'Light',
        description: 'Clean and bright interface for daytime use.',
    },
    {
        value: 'dark',
        icon: Moon,
        title: 'Dark',
        description: 'Easier on the eyes in low-light environments.',
    },
    {
        value: 'system',
        icon: Monitor,
        title: 'System',
        description: 'Automatically matches your device settings.',
    },
];

export default function AppearanceCards() {
    const { appearance, updateAppearance } = useAppearance();

    return (
        <div className="grid max-w-2xl gap-4 sm:grid-cols-3">
            {options.map((option) => {
                const selected = appearance === option.value;
                const Icon = option.icon;

                return (
                    <button
                        key={option.value}
                        type="button"
                        onClick={() => updateAppearance(option.value)}
                        aria-pressed={selected}
                        className={cn(
                            'relative rounded-xl border p-4 text-left transition-colors outline-none focus-visible:ring-2 focus-visible:ring-ring',
                            selected
                                ? 'border-primary ring-2 ring-primary/30'
                                : 'border-border hover:border-primary/50',
                        )}
                    >
                        {selected && (
                            <span className="absolute top-3 right-3 flex size-5 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                <Check className="size-3" />
                            </span>
                        )}
                        <Icon className="size-5 text-muted-foreground" />
                        <div className="mt-3 text-sm font-medium">
                            {option.title}
                        </div>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {option.description}
                        </p>
                        <div
                            className={cn(
                                'mt-4 overflow-hidden rounded-md border border-border',
                                option.value === 'dark'
                                    ? 'bg-neutral-900'
                                    : option.value === 'system'
                                      ? 'bg-gradient-to-r from-white to-neutral-900'
                                      : 'bg-white',
                            )}
                        >
                            <div className="space-y-1 p-2">
                                <div
                                    className={cn(
                                        'h-1.5 w-10 rounded',
                                        option.value === 'dark'
                                            ? 'bg-neutral-700'
                                            : 'bg-neutral-300',
                                    )}
                                />
                                <div
                                    className={cn(
                                        'h-1.5 w-16 rounded',
                                        option.value === 'dark'
                                            ? 'bg-neutral-800'
                                            : 'bg-neutral-200',
                                    )}
                                />
                            </div>
                        </div>
                    </button>
                );
            })}
        </div>
    );
}
