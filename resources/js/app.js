import './bootstrap';
import Alpine from 'alpinejs';
import QRCode from 'qrcode';

// Make QRCode globally available and add error handling
window.QRCode = QRCode;
console.log('QRCode library loaded:', window.QRCode ? 'Yes' : 'No');

// Only initialize Alpine if it hasn't been initialized yet
if (!window.Alpine) {
    window.Alpine = Alpine;
    Alpine.start();
    console.log('Alpine initialized');
}
