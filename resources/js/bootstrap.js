import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap

import $ from 'jquery';
window.$ = window.jQuery = $;

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept-Language'] = sessionStorage.getItem("locale") || import.meta.env.VITE_APP_LOCALE || import.meta.env.VITE_APP_FALLBACK_LOCALE;

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
