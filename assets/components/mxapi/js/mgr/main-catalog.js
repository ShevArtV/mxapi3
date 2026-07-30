import { createApp } from 'vue';
// Vue/PrimeVue берутся из Import Map пакета VueTools (не бандлятся).
// Всё PrimeVue — именованными импортами из единого бандла 'primevue';
// подпутей ('primevue/config') в карте нет. Тема Aura и PrimeIcons тоже
// приходят из VueTools (vuetools.css).
import { PrimeVue, Aura, ToastService, Tooltip } from 'primevue';
import './styles/shared.css';
import EndpointCatalog from './pages/EndpointCatalog.vue';

const app = createApp(EndpointCatalog);
// ⚠️ darkModeSelector: false — тёмный режим выключен намеренно. По умолчанию
// Aura следует системной схеме (prefers-color-scheme), и на машине с тёмной
// системой виджет чернел внутри всегда светлой админки MODX 3.2, которая
// собственного тёмного режима не имеет.
app.use(PrimeVue, { theme: { preset: Aura, options: { darkModeSelector: false } } });
app.use(ToastService);
app.directive('tooltip', Tooltip);
app.mount('#mxapi-app');
