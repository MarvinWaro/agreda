import { createInertiaApp } from '@inertiajs/react';
import { Toaster } from '@/components/ui/sonner';
import { TooltipProvider } from '@/components/ui/tooltip';
import { initializeTheme } from '@/hooks/use-appearance';
import AdminSettingsLayout from '@/layouts/admin/settings-layout';
import AdminLayout from '@/layouts/admin-layout';
import AppLayout from '@/layouts/app-layout';
import AuthLayout from '@/layouts/auth-layout';
import PublicLayout from '@/layouts/public-layout';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Admin pages that live inside the Settings hub (secondary sub-nav).
const adminHubPages = new Set([
    'admin/sports',
    'admin/slides',
    'admin/events',
    'admin/pages',
    'admin/faqs',
    'admin/users',
    'admin/roles',
    'admin/settings',
]);

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    layout: (name) => {
        switch (true) {
            case name === 'welcome':
                return null;
            case name.startsWith('public/'):
                return PublicLayout;
            // Settings hub: account pages + the admin config CRUDs.
            case name.startsWith('settings/') || adminHubPages.has(name):
                return [AdminLayout, AdminSettingsLayout];
            case name.startsWith('admin/'):
                return AdminLayout;
            case name.startsWith('auth/'):
                return AuthLayout;
            default:
                return AppLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return (
            <TooltipProvider delayDuration={0}>
                {app}
                <Toaster />
            </TooltipProvider>
        );
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on load...
initializeTheme();
