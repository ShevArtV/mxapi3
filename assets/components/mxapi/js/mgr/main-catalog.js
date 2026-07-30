import { createApp } from 'vue';
// Vue/PrimeVue берутся из Import Map пакета VueTools (не бандлятся).
// Всё PrimeVue — именованными импортами из единого бандла 'primevue';
// подпутей ('primevue/config') в карте нет. Тема Aura и PrimeIcons тоже
// приходят из VueTools (vuetools.css).
import { PrimeVue, Aura, ToastService, Tooltip } from 'primevue';
import EndpointCatalog from './pages/EndpointCatalog.vue';

const app = createApp(EndpointCatalog);
app.use(PrimeVue, { theme: { preset: Aura } });
app.use(ToastService);
app.directive('tooltip', Tooltip);
app.mount('#mxapi-app');
