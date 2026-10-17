import Alpine from 'alpinejs';
import createTheme from './theme-switcher';
import posRegister from './pos-register';

window.Alpine = Alpine;

if (window.April?.register) {
    window.April.register(Alpine);
}

Alpine.store('theme', createTheme(window));

Alpine.store('pwa', {
    deferredPrompt: null,
    isIOS: false,
    canPrompt: true,
    showIOSModal: false,
    showAndroidModal: false,
    dismissed: false,

    init() {
        this.isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        this.canPrompt = !this.isStandalone;

        const ua = window.navigator.userAgent.toLowerCase();
        this.isIOS = /iphone|ipad|ipod/.test(ua) || (window.navigator.platform === 'MacIntel' && window.navigator.maxTouchPoints > 1);

        try {
            const dismissedAt = localStorage.getItem('pwa_install_dismissed_at');
            if (dismissedAt && Date.now() - parseInt(dismissedAt, 10) < 24 * 60 * 60 * 1000) {
                this.dismissed = true;
            }
        } catch {
            // LocalStorage disabled or blocked
        }

        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
        });

        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.isStandalone = true;
            this.canPrompt = false;
            this.dismissed = true;
            this.showIOSModal = false;
            this.showAndroidModal = false;
        });
    },

    dismiss() {
        this.dismissed = true;
        try {
            localStorage.setItem('pwa_install_dismissed_at', Date.now().toString());
        } catch {
            // LocalStorage fallback
        }
    },

    async install() {
        if (this.isIOS) {
            this.showIOSModal = true;
            return;
        }

        if (this.deferredPrompt) {
            this.deferredPrompt.prompt();
            const choice = await this.deferredPrompt.userChoice;
            if (choice && choice.outcome === 'accepted') {
                this.dismiss();
            }
            this.deferredPrompt = null;
        } else {
            this.showAndroidModal = true;
        }
    },

    closeModals() {
        this.showIOSModal = false;
        this.showAndroidModal = false;
    },
});

Alpine.data('posRegister', posRegister);
Alpine.start();

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {
            // SW registration fallback
        });
    });
}
