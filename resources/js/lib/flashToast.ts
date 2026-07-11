import { router } from '@inertiajs/vue3';
import { toast } from 'vue-sonner';
import type { FlashToast } from '@/types/ui';

type FlashMessages = {
    success?: string;
    error?: string;
    warning?: string;
    toast?: FlashToast;
};

let isInitialized = false;
let lastToast = '';
let lastToastAt = 0;

function showToast(type: FlashToast['type'], message?: string): void {
    if (!message) {
        return;
    }

    const key = `${type}:${message}`;
    const now = Date.now();

    if (key === lastToast && now - lastToastAt < 500) {
        return;
    }

    lastToast = key;
    lastToastAt = now;
    toast[type](message);
}

function showFlashMessages(flash?: FlashMessages): void {
    if (!flash) {
        return;
    }

    if (flash.toast) {
        showToast(flash.toast.type, flash.toast.message);
    }

    showToast('success', flash.success);
    showToast('error', flash.error);
    showToast('warning', flash.warning);
}

export function initializeFlashToast(): void {
    if (isInitialized) {
        return;
    }

    isInitialized = true;

    router.on('flash', (event) => {
        showFlashMessages(event.detail.flash as FlashMessages);
    });

    router.on('success', (event) => {
        const flash = event.detail.page.props.flash as
            | FlashMessages
            | undefined;

        showFlashMessages(flash);
    });

    router.on('error', () => {
        showToast('error', 'Periksa kembali data yang dimasukkan.');
    });

    router.on('httpException', () => {
        showToast('error', 'Terjadi kesalahan pada server. Silakan coba lagi.');
    });

    router.on('networkError', () => {
        showToast(
            'error',
            'Permintaan gagal. Periksa koneksi Anda lalu coba lagi.',
        );
    });
}
