import Alpine from 'alpinejs';
import createTheme from './theme-switcher';

window.Alpine = Alpine;

Alpine.store('theme', createTheme(window));
Alpine.start();
