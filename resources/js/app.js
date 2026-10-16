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
    canInstall: false,
    init() {
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            this.deferredPrompt = e;
            this.canInstall = true;
        });
        window.addEventListener('appinstalled', () => {
            this.deferredPrompt = null;
            this.canInstall = false;
        });
    },
    async install() {
        if (!this.deferredPrompt) {
            return;
        }
        this.deferredPrompt.prompt();
        const choice = await this.deferredPrompt.userChoice;
        if (choice && choice.outcome === 'accepted') {
            this.canInstall = false;
        }
        this.deferredPrompt = null;
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
