import './bootstrap';
import { createApp } from 'vue';
import 'flyonui/flyonui';

// Import Vue components
import PasswordStrength from './components/PasswordStrength.vue';
import OnboardingWizard from './components/OnboardingWizard.vue';

// Create and configure Vue app
const app = createApp({});

// Register components globally for use in Blade templates
app.component('password-strength', PasswordStrength);
app.component('onboarding-wizard', OnboardingWizard);

// Mount Vue only if #app element exists
const appElement = document.getElementById('app');
if (appElement) {
    app.mount('#app');
}
