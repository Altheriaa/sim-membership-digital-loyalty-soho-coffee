import Alpine from 'alpinejs';
import createTheme from './theme-switcher';
import posRegister from './pos-register';

window.Alpine = Alpine;

Alpine.store('theme', createTheme(window));
Alpine.data('posRegister', posRegister);
Alpine.start();
