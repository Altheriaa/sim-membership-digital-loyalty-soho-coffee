export const THEME_STORAGE_KEY = 'theme';

/**
 * Build the theme store. "system" follows the operating system and is saved
 * by removing the key; "light" and "dark" are saved as written.
 */
export default function createTheme(browser = window) {
    const systemQuery = browser.matchMedia('(prefers-color-scheme: dark)');
    const root = browser.document.documentElement;

    const readSavedMode = () => {
        try {
            const saved = browser.localStorage.getItem(THEME_STORAGE_KEY);

            return saved === 'light' || saved === 'dark' ? saved : 'system';
        } catch {
            return 'system';
        }
    };

    const saveMode = (mode) => {
        try {
            if (mode === 'system') {
                browser.localStorage.removeItem(THEME_STORAGE_KEY);
            } else {
                browser.localStorage.setItem(THEME_STORAGE_KEY, mode);
            }
        } catch {
            // The theme still changes for this visit when storage is blocked.
        }
    };

    return {
        mode: 'system',

        init() {
            this.mode = readSavedMode();
            this.apply();
            systemQuery.addEventListener('change', () => this.apply());
        },

        set(mode) {
            this.mode = mode;
            saveMode(mode);
            this.apply();
        },

        apply() {
            const isDark = this.mode === 'dark' || (this.mode === 'system' && systemQuery.matches);

            root.classList.toggle('dark', isDark);
        },
    };
}
