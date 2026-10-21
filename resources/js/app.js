import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

// GSAP dan Lenis hanya diunduh di landing page
if (document.querySelector('[data-landing]')) {
    import('./landing').then(({ default: mulai }) => mulai());
}
