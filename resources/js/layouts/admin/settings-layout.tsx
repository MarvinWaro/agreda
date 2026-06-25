import { Link } from '@inertiajs/react';
import type { PropsWithChildren } from 'react';
import { Button } from '@/components/ui/button';
import { useCurrentUrl } from '@/hooks/use-current-url';
import { usePermissions } from '@/hooks/use-permissions';
import { cn } from '@/lib/utils';

type Item = { title: string; href: string; permission?: string };
type Group = { label: string; items: Item[] };

const groups: Group[] = [
    {
        label: 'Account',
        items: [
            { title: 'Profile', href: '/settings/profile' },
            { title: 'Password & 2FA', href: '/settings/security' },
            { title: 'Appearance', href: '/settings/appearance' },
        ],
    },
    {
        label: 'Content',
        items: [
            {
                title: 'Carousel',
                href: '/admin/slides',
                permission: 'content.manage',
            },
            {
                title: 'Events',
                href: '/admin/events',
                permission: 'content.manage',
            },
            {
                title: 'Pages',
                href: '/admin/pages',
                permission: 'content.manage',
            },
            {
                title: 'FAQs',
                href: '/admin/faqs',
                permission: 'content.manage',
            },
        ],
    },
    {
        label: 'Configuration',
        items: [
            {
                title: 'Sports & rates',
                href: '/admin/sports',
                permission: 'sports.manage',
            },
            {
                title: 'Site details',
                href: '/admin/settings',
                permission: 'settings.manage',
            },
        ],
    },
    {
        label: 'Access',
        items: [
            {
                title: 'Users',
                href: '/admin/users',
                permission: 'users.manage',
            },
            {
                title: 'Roles & permissions',
                href: '/admin/roles',
                permission: 'roles.manage',
            },
        ],
    },
];

export default function AdminSettingsLayout({ children }: PropsWithChildren) {
    const { isCurrentUrl } = useCurrentUrl();
    const { can } = usePermissions();

    const visibleGroups = groups
        .map((group) => ({
            ...group,
            items: group.items.filter(
                (item) => !item.permission || can(item.permission),
            ),
        }))
        .filter((group) => group.items.length > 0);

    return (
        <div className="p-4 sm:p-6">
            <div className="flex flex-col gap-6 lg:flex-row lg:gap-8">
                <aside className="lg:w-56 lg:shrink-0">
                    <nav
                        aria-label="Settings"
                        className="flex flex-col gap-5 lg:sticky lg:top-20"
                    >
                        {visibleGroups.map((group) => (
                            <div key={group.label}>
                                <p className="px-3 pb-1 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    {group.label}
                                </p>
                                <div className="flex flex-col gap-1">
                                    {group.items.map((item) => (
                                        <Button
                                            key={item.href}
                                            asChild
                                            variant="ghost"
                                            size="sm"
                                            className={cn(
                                                'w-full justify-start',
                                                isCurrentUrl(item.href) &&
                                                    'bg-muted',
                                            )}
                                        >
                                            <Link href={item.href}>
                                                {item.title}
                                            </Link>
                                        </Button>
                                    ))}
                                </div>
                            </div>
                        ))}
                    </nav>
                </aside>

                <div className="min-w-0 flex-1">{children}</div>
            </div>
        </div>
    );
}
