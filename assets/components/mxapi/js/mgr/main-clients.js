import { createApp } from 'vue';
import { PrimeVue, Aura, ConfirmationService, ToastService, Tooltip } from 'primevue';
import './styles/shared.css';
import UserClients from './pages/UserClients.vue';
import { t } from './utils/i18n.js';

/**
 * Вкладка «mxApi» на странице правки пользователя.
 *
 * Сама вкладка добавляется средствами ExtJS — панель вкладок пользователя чужая,
 * и вставить в неё свой DOM иначе нельзя. Содержимое вкладки — обычный контейнер,
 * в который монтируется Vue-приложение: так интерфейс клиентов живёт на том же
 * стеке, что и остальная админка пакета.
 */

const cfg = () => window.MxApiUserClients || {};
const TAB_ID = 'mxapi-user-clients-tab';
const MOUNT_ID = 'mxapi-user-clients-app';

function mountApp() {
  const el = document.getElementById(MOUNT_ID);
  if (!el || el.dataset.mxapiMounted === '1') return;
  el.dataset.mxapiMounted = '1';

  const app = createApp(UserClients);
  // ⚠️ darkModeSelector: false — тёмный режим выключен намеренно. По умолчанию
// Aura следует системной схеме (prefers-color-scheme), и на машине с тёмной
// системой виджет чернел внутри всегда светлой админки MODX 3.2, которая
// собственного тёмного режима не имеет.
app.use(PrimeVue, { theme: { preset: Aura, options: { darkModeSelector: false } } });
  app.use(ConfirmationService);
  app.use(ToastService);
  app.directive('tooltip', Tooltip);
  app.mount(el);
}

function attach(tabs) {
  if (Ext.getCmp(TAB_ID)) return;

  tabs.add({
    title: t('mxapi'),
    id: TAB_ID,
    layout: 'anchor',
    bodyStyle: 'padding:10px',
    // Класс vueApp обязателен: стили PrimeVue из VueTools префиксованы им.
    html: `<div id="${MOUNT_ID}" class="vueApp"></div>`,
    listeners: {
      // Монтируем лениво: на страницу пользователя заходят и не ради API, а до
      // активации вкладки её DOM в ExtJS ещё не отрисован.
      activate: {
        fn: () => setTimeout(mountApp, 0),
        single: false,
      },
    },
  });

  tabs.doLayout();
}

/**
 * Панель вкладок пользователя. Штатный id — modx-user-tabs; если тема менеджера
 * его сменит, ищем первую панель вкладок на странице, чтобы вкладка не пропала
 * молча.
 */
function findTabs() {
  const byId = Ext.getCmp('modx-user-tabs');
  if (byId) return byId;

  let found = null;
  Ext.ComponentMgr.all.each((cmp) => {
    if (!found && cmp.getXType && cmp.getXType() === 'modx-vtabs') {
      found = cmp;
    }
    return true;
  });

  return found;
}

Ext.onReady(() => {
  if (!cfg().user_id) return;

  // ⚠️ Ext.ComponentMgr.onAvailable срабатывает только на БУДУЩЕЕ добавление
  // компонента и не проверяет уже зарегистрированные. Модуль грузится как ESM,
  // то есть заведомо после сборки страницы, — поэтому сначала берём готовую
  // панель, и только если её нет, подписываемся на появление.
  const existing = findTabs();
  if (existing) {
    attach(existing);
    return;
  }

  Ext.ComponentMgr.onAvailable('modx-user-tabs', attach);
});
