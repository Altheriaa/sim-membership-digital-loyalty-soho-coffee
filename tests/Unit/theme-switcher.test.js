import { test } from 'node:test';
import assert from 'node:assert/strict';
import createTheme from '../../resources/js/theme-switcher.js';

function fakeBrowser({ saved = null, systemIsDark = false, storageBlocked = false } = {}) {
    const classes = new Set();
    const storage = new Map(saved ? [['theme', saved]] : []);
    const listeners = [];
    const systemQuery = {
        matches: systemIsDark,
        addEventListener: (_event, listener) => listeners.push(listener),
    };

    const guard = () => {
        if (storageBlocked) {
            throw new Error('Storage blocked');
        }
    };

    return {
        storage,
        systemQuery,
        notifySystemChange: () => listeners.forEach((listener) => listener()),
        isDark: () => classes.has('dark'),
        matchMedia: () => systemQuery,
        document: {
            documentElement: {
                classList: {
                    toggle: (name, enabled) => (enabled ? classes.add(name) : classes.delete(name)),
                },
            },
        },
        localStorage: {
            getItem: (key) => {
                guard();

                return storage.get(key) ?? null;
            },
            setItem: (key, value) => {
                guard();
                storage.set(key, value);
            },
            removeItem: (key) => {
                guard();
                storage.delete(key);
            },
        },
    };
}

function startedTheme(options) {
    const browser = fakeBrowser(options);
    const theme = createTheme(browser);

    theme.init();

    return { browser, theme };
}

test('starts in system mode and follows a dark operating system', () => {
    const { browser, theme } = startedTheme({ systemIsDark: true });

    assert.equal(theme.mode, 'system');
    assert.equal(browser.isDark(), true);
});

test('starts in system mode and stays light on a light operating system', () => {
    const { browser, theme } = startedTheme({ systemIsDark: false });

    assert.equal(theme.mode, 'system');
    assert.equal(browser.isDark(), false);
});

test('restores a saved dark choice even when the system is light', () => {
    const { browser, theme } = startedTheme({ saved: 'dark', systemIsDark: false });

    assert.equal(theme.mode, 'dark');
    assert.equal(browser.isDark(), true);
});

test('restores a saved light choice even when the system is dark', () => {
    const { browser, theme } = startedTheme({ saved: 'light', systemIsDark: true });

    assert.equal(theme.mode, 'light');
    assert.equal(browser.isDark(), false);
});

test('ignores an unknown saved value and falls back to system', () => {
    const { theme } = startedTheme({ saved: 'sepia' });

    assert.equal(theme.mode, 'system');
});

test('saves light and dark, and clears the saved value for system', () => {
    const { browser, theme } = startedTheme({ systemIsDark: true });

    theme.set('light');
    assert.equal(browser.storage.get('theme'), 'light');
    assert.equal(browser.isDark(), false);

    theme.set('dark');
    assert.equal(browser.storage.get('theme'), 'dark');
    assert.equal(browser.isDark(), true);

    theme.set('system');
    assert.equal(browser.storage.has('theme'), false);
    assert.equal(browser.isDark(), true);
});

test('follows operating system changes only while in system mode', () => {
    const { browser, theme } = startedTheme({ systemIsDark: false });

    browser.systemQuery.matches = true;
    browser.notifySystemChange();
    assert.equal(browser.isDark(), true);

    theme.set('light');
    browser.systemQuery.matches = true;
    browser.notifySystemChange();
    assert.equal(browser.isDark(), false);
});

test('still switches themes when storage is blocked', () => {
    const { browser, theme } = startedTheme({ storageBlocked: true });

    theme.set('dark');

    assert.equal(theme.mode, 'dark');
    assert.equal(browser.isDark(), true);
});
