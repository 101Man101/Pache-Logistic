import { createInertiaApp } from '@inertiajs/vue3';
import { initializeTheme } from '@/composables/useAppearance';
import ErpLayout from '@/layouts/ErpLayout.vue';
import { initializeFlashToast } from '@/lib/flashToast';

const appName = import.meta.env.VITE_APP_NAME || 'Pache Logistic';

void createInertiaApp({
    title: (title) => (title ? `${title} — ${appName}` : appName),
    layout: (name) => {
        if (
            name === 'Welcome' ||
            name === 'PublicTracking' ||
            name === 'oms/Label' ||
            name === 'finance/InvoicePrint' ||
            name === 'finance/VendorBillPrint' ||
            name.startsWith('auth/')
        ) {
            return null;
        }

        return ErpLayout;
    },
    withApp: (app) => {
        app.directive('focus', {
            mounted: (el: HTMLElement, shouldFocus) => {
                if (shouldFocus.value !== false) {
                    el.focus();
                }
            },
        });
    },
    progress: {
        color: '#2563eb',
    },
});

initializeTheme();
initializeFlashToast();
