<script setup>
import { computed, onMounted, ref } from 'vue';
import { Button, InputText, Message, Select, Tag, Toast } from 'primevue';
import { CatalogApi, config, errorMessage, listOf } from '../api/connector.js';
import { t } from '../utils/i18n.js';
import EndpointDetails from '../components/EndpointDetails.vue';

const cfg = config();

const endpoints = ref([]);
const loading = ref(true);
const error = ref('');
const query = ref('');
// Пустая строка, а не null: у Select с option-value = null подпись выбранного
// пункта не отображается — селектор выглядит пустым.
const provider = ref('');
const expanded = ref({});

const baseUrl = computed(() => `${cfg.site_url || ''}${cfg.route_prefix || ''}`);

// Источники для селектора: только те, что реально встретились в каталоге —
// список провайдеров зависит от установленных пакетов, а не от справочника.
const providers = computed(() => {
  const names = [...new Set(endpoints.value.map((e) => e.provider).filter(Boolean))].sort();
  return [{ label: t('mxapi_catalog_all_providers'), value: '' }, ...names.map((n) => ({ label: n, value: n }))];
});

const filtering = computed(() => query.value.trim() !== '' || provider.value !== '');

const filtered = computed(() => {
  const needle = query.value.trim().toLowerCase();
  return endpoints.value.filter((endpoint) => {
    if (provider.value && endpoint.provider !== provider.value) return false;
    if (!needle) return true;
    return [endpoint.id, endpoint.title, endpoint.path, endpoint.scope, endpoint.permission, endpoint.description]
      .join(' ')
      .toLowerCase()
      .includes(needle);
  });
});

async function load() {
  loading.value = true;
  error.value = '';
  try {
    endpoints.value = listOf(await CatalogApi.getList());
  } catch (e) {
    error.value = errorMessage(e, t('mxapi_catalog_error'));
  } finally {
    loading.value = false;
  }
}

function toggle(id) {
  expanded.value = { ...expanded.value, [id]: !expanded.value[id] };
}

function resetFilters() {
  query.value = '';
  provider.value = '';
}

/**
 * Выгрузка OpenAPI файлом.
 *
 * Отправляется формой, а не ссылкой: коннектору нужен HTTP_MODAUTH, а в адресной
 * строке токену сессии не место — он попал бы в историю браузера и логи прокси.
 */
function downloadOpenApi() {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = cfg.connector_url;
  form.target = '_blank';
  form.style.display = 'none';

  const fields = {
    action: 'MxApi\\Processors\\Mgr\\OpenApi\\Get',
    download: '1',
    HTTP_MODAUTH: cfg.token || '',
  };
  for (const [name, value] of Object.entries(fields)) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
  document.body.removeChild(form);
}

// Метод — главный признак того, что делает эндпоинт, поэтому цвет несёт смысл:
// чтение, изменение, удаление. Декоративных оттенков здесь нет.
function methodSeverity(method) {
  switch (method) {
    case 'GET':
      return 'info';
    case 'POST':
    case 'PUT':
    case 'PATCH':
      return 'warn';
    case 'DELETE':
      return 'danger';
    default:
      return 'secondary';
  }
}

onMounted(load);
</script>

<template>
  <div class="mxapi-app mxapi-catalog">
    <Toast />

    <Message v-if="!cfg.enabled" severity="warn" :closable="false" class="mxapi-block">
      {{ t('mxapi_catalog_disabled') }}
    </Message>

    <div class="mxapi-toolbar">
      <InputText
        v-model="query"
        :placeholder="t('mxapi_catalog_search_ph')"
        :aria-label="t('mxapi_catalog_search_ph')"
        class="mxapi-search"
      />
      <Select
        v-model="provider"
        :options="providers"
        option-label="label"
        option-value="value"
        :aria-label="t('mxapi_catalog_all_providers')"
        :placeholder="t('mxapi_catalog_all_providers')"
        class="mxapi-provider"
      />
      <Button
        :label="t('mxapi_catalog_download_openapi')"
        icon="pi pi-download"
        severity="secondary"
        outlined
        @click="downloadOpenApi"
      />
    </div>

    <p class="mxapi-base">
      {{ t('mxapi_catalog_base_url') }} <code>{{ baseUrl }}</code>
      <span class="mxapi-muted">
        · {{ t('mxapi_catalog_count', { shown: filtered.length, total: endpoints.length }) }}
      </span>
    </p>

    <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>

    <!-- Скелетон, а не строка «загрузка»: список появляется на своём месте и
         страница не дёргается. -->
    <div v-else-if="loading" class="mxapi-list" aria-busy="true" :aria-label="t('mxapi_catalog_loading')">
      <div v-for="n in 4" :key="n" class="mxapi-skeleton"></div>
    </div>

    <!-- Пустых состояния два, и они означают разное: на сайте нет ни одного
         эндпоинта (нужен провайдер) или фильтр слишком узкий (нужно его снять). -->
    <div v-else-if="!endpoints.length" class="mxapi-empty">
      <p class="mxapi-empty-title">{{ t('mxapi_catalog_empty_none') }}</p>
      <p class="mxapi-muted">{{ t('mxapi_catalog_empty_none_hint') }}</p>
    </div>

    <div v-else-if="!filtered.length" class="mxapi-empty">
      <p class="mxapi-empty-title">{{ t('mxapi_catalog_empty') }}</p>
      <Button
        v-if="filtering"
        :label="t('mxapi_catalog_reset_filters')"
        icon="pi pi-filter-slash"
        severity="secondary"
        text
        @click="resetFilters"
      />
    </div>

    <div v-else class="mxapi-list">
      <article
        v-for="endpoint in filtered"
        :key="endpoint.id"
        class="mxapi-item"
        :class="{ 'mxapi-item-open': expanded[endpoint.id] }"
      >
        <!-- Кнопка, а не div с @click: раскрытие обязано работать с клавиатуры
             и объявлять своё состояние скринридеру. -->
        <button
          type="button"
          class="mxapi-item-head"
          :aria-expanded="!!expanded[endpoint.id]"
          :aria-controls="`mxapi-details-${endpoint.id}`"
          @click="toggle(endpoint.id)"
        >
          <i
            class="pi mxapi-chevron"
            :class="expanded[endpoint.id] ? 'pi-chevron-down' : 'pi-chevron-right'"
            aria-hidden="true"
          ></i>
          <span class="mxapi-methods">
            <Tag
              v-for="method in endpoint.methods"
              :key="method"
              :value="method"
              :severity="methodSeverity(method)"
            />
            <Tag
              v-if="!endpoint.public"
              :value="t('mxapi_badge_internal')"
              severity="secondary"
              :title="t('mxapi_badge_internal_hint')"
            />
            <Tag v-if="endpoint.write" :value="t('mxapi_badge_write')" severity="warn" />
            <Tag v-if="endpoint.deprecated" :value="t('mxapi_badge_deprecated')" severity="danger" />
          </span>
          <code class="mxapi-path">{{ endpoint.public_path || endpoint.path }}</code>
          <span class="mxapi-title">{{ endpoint.title }}</span>
          <span class="mxapi-provider-tag mxapi-muted">{{ endpoint.provider }}</span>
        </button>

        <EndpointDetails
          v-if="expanded[endpoint.id]"
          :id="`mxapi-details-${endpoint.id}`"
          :endpoint="endpoint"
          :base-url="baseUrl"
        />
      </article>
    </div>
  </div>
</template>

<style scoped>
.mxapi-catalog {
  padding: 1rem;
}

.mxapi-block {
  margin-bottom: 1rem;
}

.mxapi-toolbar {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.mxapi-search {
  flex: 1 1 20rem;
}

.mxapi-provider {
  min-width: 14rem;
}

.mxapi-base {
  margin: 0.75rem 0 1rem;
}

.mxapi-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mxapi-item {
  border: 1px solid var(--p-content-border-color);
  border-radius: var(--p-content-border-radius, 6px);
  overflow: hidden;
  background: var(--p-content-background);
}

.mxapi-item-head {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  flex-wrap: wrap;
  width: 100%;
  /* Высота строки держит цель нажатия не меньше 44px, как на тач-устройствах. */
  min-height: 2.75rem;
  padding: 0.6rem 0.75rem;
  border: 0;
  background: transparent;
  color: inherit;
  font: inherit;
  text-align: left;
  cursor: pointer;
  transition: background-color 160ms cubic-bezier(0.22, 1, 0.36, 1);
}

.mxapi-item-head:hover {
  background: var(--p-content-hover-background);
}

.mxapi-item-head:focus-visible {
  outline: 2px solid var(--p-primary-color);
  outline-offset: -2px;
}

.mxapi-item-open .mxapi-item-head {
  border-bottom: 1px solid var(--p-content-border-color);
}

.mxapi-chevron {
  font-size: 0.75rem;
  color: var(--p-text-muted-color);
  transition: transform 160ms cubic-bezier(0.22, 1, 0.36, 1);
}

.mxapi-methods {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.mxapi-path {
  font-weight: 600;
}

.mxapi-title {
  flex: 1 1 12rem;
}

.mxapi-provider-tag {
  font-size: 0.85em;
}

.mxapi-empty {
  padding: 2rem 1rem;
  text-align: center;
  border: 1px dashed var(--p-content-border-color);
  border-radius: var(--p-content-border-radius, 6px);
}

.mxapi-empty-title {
  margin: 0 0 0.35rem;
  font-weight: 600;
}

.mxapi-empty p {
  margin: 0 0 0.5rem;
}

@media (prefers-reduced-motion: reduce) {
  .mxapi-item-head,
  .mxapi-chevron {
    transition: none;
  }
}
</style>
