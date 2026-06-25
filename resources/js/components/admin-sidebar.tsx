import { Link } from '@inertiajs/react';
import {
    CalendarCheck,
    LayoutDashboard,
    Settings,
    ShieldCheck,
    SquareArrowOutUpRight,
    Tag,
    Users,
} from 'lucide-react';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

const navGroups: { label: string; items: NavItem[] }[] = [
    {
        label: 'Manage',
        items: [
            { title: 'Dashboard', href: '/admin', icon: LayoutDashboard },
            { title: 'Bookings', href: '/admin/bookings', icon: CalendarCheck },
            { title: 'Sports & rates', href: '/admin/sports', icon: Tag },
        ],
    },
    {
        label: 'User management',
        items: [
            { title: 'Users', href: '/admin/users', icon: Users },
            {
                title: 'Roles & permissions',
                href: '/admin/roles',
                icon: ShieldCheck,
            },
        ],
    },
    {
        label: 'System',
        items: [{ title: 'Settings', href: '/admin/settings', icon: Settings }],
    },
];

export function AdminSidebar() {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/admin" prefetch>
                                <span className="flex items-center gap-2 font-bold tracking-tight">
                                    <span>AGREDA</span>
                                    <span className="rounded-md bg-primary px-1.5 py-0.5 text-xs font-semibold text-primary-foreground">
                                        Admin
                                    </span>
                                </span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                {navGroups.map((group) => (
                    <SidebarGroup key={group.label} className="px-2 py-0">
                        <SidebarGroupLabel>{group.label}</SidebarGroupLabel>
                        <SidebarMenu>
                            {group.items.map((item) => (
                                <SidebarMenuItem key={item.title}>
                                    <SidebarMenuButton
                                        asChild
                                        isActive={isCurrentUrl(item.href)}
                                        tooltip={{ children: item.title }}
                                    >
                                        <Link href={item.href} prefetch>
                                            {item.icon && <item.icon />}
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>
                                </SidebarMenuItem>
                            ))}
                        </SidebarMenu>
                    </SidebarGroup>
                ))}
            </SidebarContent>

            <SidebarFooter>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton
                            asChild
                            tooltip={{ children: 'View site' }}
                        >
                            <Link href="/">
                                <SquareArrowOutUpRight />
                                <span>View site</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
