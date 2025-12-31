import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Echo/Pusher disabled - not in use
// To enable real-time features, set VITE_REVERB_APP_KEY in .env
