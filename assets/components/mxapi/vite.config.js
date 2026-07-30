import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import { resolve } from 'path';

export default defineConfig({
  plugins: [vue()],
  build: {
    outDir: 'js/mgr/vue-dist',
    emptyOutDir: true,
    rollupOptions: {
      // Vue/Pinia/PrimeVue и composables приходят из Import Map пакета
      // VueTools — НЕ бандлим их, в выхлопе остаётся только код приложения.
      external: ['vue', 'pinia', 'primevue', /^@vuetools\//],
      input: {
        // Каталог эндпоинтов — своя страница CMP.
        catalog: resolve(__dirname, 'js/mgr/main-catalog.js'),
        // Клиенты интеграции — вкладка внутри страницы правки пользователя.
        clients: resolve(__dirname, 'js/mgr/main-clients.js'),
      },
      output: {
        entryFileNames: '[name].min.js',
        chunkFileNames: '[name].min.js',
        // Общий код обоих приложений (HTTP-слой, лексикон, хелпер Vue) — в один
        // предсказуемо названный чанк: иначе rollup выдаёт файл вида
        // _plugin-vue_export-helper.min.js, который непонятно как трактовать в
        // transport и в отладке.
        manualChunks(id) {
          if (
            id.includes('/js/mgr/api/') ||
            id.includes('/js/mgr/utils/') ||
            id.includes('plugin-vue:export-helper')
          ) {
            return 'shared';
          }
          return undefined;
        },
        assetFileNames: (assetInfo) => {
          if (assetInfo.name && assetInfo.name.endsWith('.css')) {
            return assetInfo.name.replace('.css', '.min.css');
          }
          return '[name].[ext]';
        },
      },
    },
  },
});
