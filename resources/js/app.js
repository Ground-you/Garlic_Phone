import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName =
    import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .directive('click-outside', {
                mounted(el, binding) {
                    el._clickOutside = (e) => {
                        if (!el.contains(e.target)) binding.value();
                    };
                    document.addEventListener('click', el._clickOutside);
                },
                unmounted(el) {
                    document.removeEventListener('click', el._clickOutside);
                }
            })
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});