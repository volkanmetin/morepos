//import './bootstrap';
//import '../css/app.css';
//import 'bootstrap/dist/css/bootstrap.min.css'
import './bootstrap';
import '../scss/config/material/app.scss';
import '../scss/mermaid.min.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/index.js';
import BootstrapVueNext from 'bootstrap-vue-next';
import DataTable from 'datatables.net-vue3';
import DataTablesCore from 'datatables.net-bs5';
import draggable from 'vuedraggable';

DataTable.use(DataTablesCore);
import store from './store'
import i18n from "./i18n.js";
//import { InertiaProgress } from '@inertiajs/progress';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(store)
            .use(ZiggyVue)
            .use(i18n)
            .use(BootstrapVueNext)
            .component('DataTable', DataTable)
            .component('draggable', draggable)
            .mount(el);
    },
    progress: {
        color: '#f6a801',
        // The delay after which the progress bar will appear, in milliseconds...
        delay: 250,
        // Whether to include the default NProgress styles...
        includeCSS: true,
        // Whether the NProgress spinner will be shown...
        showSpinner: true,
    },
});

import '@fortawesome/fontawesome-free/css/all.css'
