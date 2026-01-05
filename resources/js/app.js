import './bootstrap';
import 'flyonui/flyonui';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

// FlyonUI auto-init
document.addEventListener('DOMContentLoaded', function() {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
});
