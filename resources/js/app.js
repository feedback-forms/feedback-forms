import './bootstrap';
import Alpine from 'alpinejs';

// Only initialize Alpine if it hasn't been initialized yet
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
}
