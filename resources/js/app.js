import './bootstrap';
import 'flyonui/flyonui';

// FlyonUI auto-init
document.addEventListener('DOMContentLoaded', function() {
    if (window.HSStaticMethods) {
        window.HSStaticMethods.autoInit();
    }
});
