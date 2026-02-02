import './bootstrap';
import 'flyonui/flyonui';
import Alpine from 'alpinejs';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.min.css';

// Make Alpine available globally
window.Alpine = Alpine;

// Make flatpickr available globally
window.flatpickr = flatpickr;

// Start Alpine
Alpine.start();

// FlyonUI auto-init
document.addEventListener('DOMContentLoaded', function() {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }

    // Auto-initialize flatpickr on elements with data-datepicker attribute
    document.querySelectorAll('[data-datepicker]').forEach(function(el) {
        const options = {
            dateFormat: 'Y-m-d',
            monthSelectorType: 'static',
            altInput: true,
            altFormat: 'F j, Y',
        };

        // Merge with custom options from data attribute
        const customOptions = el.dataset.datepickerOptions ? JSON.parse(el.dataset.datepickerOptions) : {};
        flatpickr(el, { ...options, ...customOptions });
    });
});
