import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo with Reverb configuration
// Only initialize if VITE_REVERB_APP_KEY is set in .env
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey && reverbKey !== '' && reverbKey !== 'undefined') {
    // Dynamically import to avoid errors when key is not set
    Promise.all([
        import('pusher-js'),
        import('laravel-echo')
    ]).then(([Pusher, Echo]) => {
        window.Pusher = Pusher.default;
        window.Echo = new Echo.default({
            broadcaster: 'reverb',
            key: reverbKey,
            wsHost: import.meta.env.VITE_REVERB_HOST,
            wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
            wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
            forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
            enabledTransports: ['ws', 'wss'],
        });
    }).catch(() => {
        // Silently fail if Echo/Pusher can't be loaded
    });
}
