import { Link, router, usePage } from '@inertiajs/react';
import { Facebook, Menu, Phone } from 'lucide-react';
import type { PropsWithChildren } from 'react';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetClose,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
} from '@/components/ui/sheet';
import { usePermissions } from '@/hooks/use-permissions';
import { cn } from '@/lib/utils';
import { dashboard, login, logout, register } from '@/routes';

type NavLink = {
    title: string;
    href: string;
};

const navLinks: NavLink[] = [
    { title: 'Home', href: '/' },
    { title: 'About', href: '/about' },
    { title: 'Facilities', href: '/facilities' },
    { title: 'Pricing', href: '/pricing' },
    { title: 'FAQ', href: '/faqs' },
    { title: 'Contact', href: '/contact' },
];

function Wordmark() {
    return (
        <Link
            href="/"
            className="flex items-center gap-2 font-bold tracking-tight"
            aria-label="AGREDA Phase III — home"
        >
            <span className="text-lg text-foreground">AGREDA</span>
            <span className="rounded-md bg-primary px-1.5 py-0.5 text-sm font-semibold text-primary-foreground">
                III
            </span>
        </Link>
    );
}

export default function PublicLayout({ children }: PropsWithChildren) {
    const page = usePage();
    const url = page.url;
    const user = page.props.auth.user;
    const { can } = usePermissions();
    const isAdmin = can('admin.access');

    const isActive = (href: string): boolean =>
        href === '/' ? url === '/' : url.startsWith(href);

    const handleLogout = (): void => {
        router.flushAll();
    };

    return (
        <div className="relative flex min-h-screen flex-col bg-background text-foreground">
            <div
                aria-hidden
                className="pointer-events-none absolute inset-0 z-0 [background-image:linear-gradient(to_right,var(--grid)_1px,transparent_1px),linear-gradient(to_bottom,var(--grid)_1px,transparent_1px)] [mask-image:radial-gradient(ellipse_at_top,#000_30%,transparent_75%)] [background-size:48px_48px]"
            />

            <header className="sticky top-0 z-40 border-b border-border bg-background/85 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div className="mx-auto flex h-16 w-full max-w-6xl items-center justify-between gap-4 px-4 sm:px-6">
                    <Wordmark />

                    {/* Desktop nav */}
                    <nav
                        aria-label="Primary"
                        className="hidden items-center gap-1 md:flex"
                    >
                        {navLinks.map((link) => (
                            <Link
                                key={link.href}
                                href={link.href}
                                className={cn(
                                    'rounded-md px-3 py-2 text-sm font-medium transition-colors hover:text-primary',
                                    isActive(link.href)
                                        ? 'text-primary'
                                        : 'text-muted-foreground',
                                )}
                            >
                                {link.title}
                            </Link>
                        ))}
                    </nav>

                    <div className="flex items-center gap-2">
                        {user ? (
                            isAdmin ? (
                                <Button
                                    asChild
                                    variant="ghost"
                                    className="hidden sm:inline-flex"
                                >
                                    <Link href={dashboard()}>Dashboard</Link>
                                </Button>
                            ) : (
                                <Button
                                    asChild
                                    variant="ghost"
                                    className="hidden sm:inline-flex"
                                >
                                    <Link
                                        href={logout()}
                                        as="button"
                                        onClick={handleLogout}
                                    >
                                        Log out
                                    </Link>
                                </Button>
                            )
                        ) : (
                            <>
                                <Button
                                    asChild
                                    variant="ghost"
                                    className="hidden sm:inline-flex"
                                >
                                    <Link href={login()}>Log in</Link>
                                </Button>
                                <Button
                                    asChild
                                    variant="outline"
                                    className="hidden sm:inline-flex"
                                >
                                    <Link href={register()}>Get started</Link>
                                </Button>
                            </>
                        )}
                        <Button asChild className="hidden sm:inline-flex">
                            <Link href="/book">Book now</Link>
                        </Button>

                        {/* Mobile menu */}
                        <Sheet>
                            <SheetTrigger asChild>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    className="md:hidden"
                                    aria-label="Open menu"
                                >
                                    <Menu className="size-5" />
                                </Button>
                            </SheetTrigger>
                            <SheetContent side="right" className="w-72">
                                <SheetHeader>
                                    <SheetTitle className="text-left">
                                        <Wordmark />
                                    </SheetTitle>
                                </SheetHeader>
                                <nav
                                    aria-label="Mobile"
                                    className="mt-2 flex flex-col gap-1 px-4"
                                >
                                    {navLinks.map((link) => (
                                        <SheetClose asChild key={link.href}>
                                            <Link
                                                href={link.href}
                                                className={cn(
                                                    'flex h-11 items-center rounded-md px-3 text-base font-medium transition-colors hover:bg-accent',
                                                    isActive(link.href)
                                                        ? 'text-primary'
                                                        : 'text-foreground',
                                                )}
                                            >
                                                {link.title}
                                            </Link>
                                        </SheetClose>
                                    ))}

                                    <div className="my-2 border-t border-border" />

                                    {user ? (
                                        isAdmin ? (
                                            <SheetClose asChild>
                                                <Link
                                                    href={dashboard()}
                                                    className="flex h-11 items-center rounded-md px-3 text-base font-medium text-foreground transition-colors hover:bg-accent"
                                                >
                                                    Dashboard
                                                </Link>
                                            </SheetClose>
                                        ) : (
                                            <SheetClose asChild>
                                                <Link
                                                    href={logout()}
                                                    as="button"
                                                    onClick={handleLogout}
                                                    className="flex h-11 w-full items-center rounded-md px-3 text-left text-base font-medium text-foreground transition-colors hover:bg-accent"
                                                >
                                                    Log out
                                                </Link>
                                            </SheetClose>
                                        )
                                    ) : (
                                        <>
                                            <SheetClose asChild>
                                                <Link
                                                    href={login()}
                                                    className="flex h-11 items-center rounded-md px-3 text-base font-medium text-foreground transition-colors hover:bg-accent"
                                                >
                                                    Log in
                                                </Link>
                                            </SheetClose>
                                            <SheetClose asChild>
                                                <Link
                                                    href={register()}
                                                    className="flex h-11 items-center rounded-md px-3 text-base font-medium text-foreground transition-colors hover:bg-accent"
                                                >
                                                    Get started
                                                </Link>
                                            </SheetClose>
                                        </>
                                    )}
                                    <SheetClose asChild>
                                        <Button asChild className="mt-3 h-11">
                                            <Link href="/book">Book now</Link>
                                        </Button>
                                    </SheetClose>
                                </nav>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </header>

            <main className="relative z-10 flex-1">{children}</main>

            <footer className="relative z-10 border-t border-border bg-muted/30">
                <div className="mx-auto grid w-full max-w-6xl gap-8 px-4 py-12 sm:px-6 md:grid-cols-3">
                    <div className="space-y-3">
                        <Wordmark />
                        <p className="max-w-xs text-sm text-muted-foreground">
                            One court, four sports. Book Basketball, Volleyball,
                            Futsal or Pickleball — confirmed by the owner,
                            payment in person.
                        </p>
                    </div>

                    <div>
                        <h2 className="text-sm font-semibold text-foreground">
                            Explore
                        </h2>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            {navLinks.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        className="transition-colors hover:text-primary"
                                    >
                                        {link.title}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div>
                        <h2 className="text-sm font-semibold text-foreground">
                            Get in touch
                        </h2>
                        <ul className="mt-3 space-y-2 text-sm text-muted-foreground">
                            <li className="flex items-center gap-2">
                                <Phone className="size-4 text-primary" />
                                <a
                                    href="tel:09170000000"
                                    className="transition-colors hover:text-primary"
                                >
                                    0917 000 0000
                                </a>
                            </li>
                            <li className="flex items-center gap-2">
                                <Facebook className="size-4 text-primary" />
                                <a
                                    href="https://facebook.com/agreda"
                                    target="_blank"
                                    rel="noreferrer"
                                    className="transition-colors hover:text-primary"
                                >
                                    fb.com/agreda
                                </a>
                            </li>
                            <li className="text-muted-foreground">
                                Open daily 8:00 AM – 10:00 PM
                            </li>
                        </ul>
                    </div>
                </div>

                <div className="border-t border-border">
                    <div className="mx-auto w-full max-w-6xl px-4 py-4 text-center text-xs text-muted-foreground sm:px-6">
                        © {new Date().getFullYear()} AGREDA Phase III. All
                        rights reserved.
                    </div>
                </div>
            </footer>
        </div>
    );
}
