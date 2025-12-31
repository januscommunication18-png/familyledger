import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Laravel Echo with Reverb configuration (only initialize if key is available)
const reverbKey = import.meta.env.VITE_REVERB_APP_KEY;

if (reverbKey) {
    import('pusher-js').then((Pusher) => {
        import('laravel-echo').then((Echo) => {
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
        });
    });
}
