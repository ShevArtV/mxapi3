<script setup>
import { computed, onMounted, ref } from 'vue';
import { Button, InputText, Message, Select, Tag } from 'primevue';
import { CatalogApi, config, errorMessage, listOf } from '../api/connector.js';
import { t } from '../utils/i18n.js';
import EndpointDetails from '../components/EndpointDetails.vue';

const cfg = config();

const endpoints = ref([]);
const loading = ref(true);
const error = ref('');
const query = ref('');
const provider = ref(null);
const expanded = ref({});

const baseUrl = computed(() => `${cfg.site_url || ''}${cfg.route_prefix || ''}`);

// Источники для селектора: только те, что реально встретились в каталоге —
// список провайдеров зависит от установленных пакетов, а не от справочника.
const providers = computed(() => {
  const names = [...new Set(endpoints.value.map((e) => e.provider).filter(Boolean))].sort();
  return [{ label: t('mxapi_catalog_all_providers'), value: null }, ...names.map((n) => ({ label: n, value: n }))];
});

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
  <div class="mxapi-catalog">
    <Message v-if="!cfg.enabled" severity="warn" :closable="false" class="mxapi-mb">
      {{ t('mxapi_catalog_disabled') }}
    </Message>

    <div class="mxapi-toolbar">
      <InputText v-model="query" :placeholder="t('mxapi_catalog_search_ph')" class="mxapi-search" />
      <Select
        v-model="provider"
        :options="providers"
        option-label="label"
        option-value="value"
        class="mxapi-provider"
      />
      <Button
        :label="t('mxapi_catalog_download_openapi')"
        icon="pi pi-download"
        severity="secondary"
        @click="downloadOpenApi"
      />
    </div>

    <div class="mxapi-base">
      {{ t('mxapi_catalog_base_url') }} <code>{{ baseUrl }}</code>
      <span class="mxapi-muted">
        · {{ t('mxapi_catalog_count', { shown: filtered.length, total: endpoints.length }) }}
      </span>
    </div>

    <Message v-if="error" severity="error" :closable="false">{{ error }}</Message>
    <div v-else-if="loading" class="mxapi-muted">{{ t('mxapi_catalog_loading') }}</div>
    <div v-else-if="!filtered.length" class="mxapi-muted">{{ t('mxapi_catalog_empty') }}</div>

    <div v-else class="mxapi-list">
      <div
        v-for="endpoint in filtered"
        :key="endpoint.id"
        class="mxapi-item"
        :class="{ 'mxapi-item-open': expanded[endpoint.id] }"
      >
        <div class="mxapi-item-head" @click="toggle(endpoint.id)">
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
              v-tooltip.top="t('mxapi_badge_internal_hint')"
            />
            <Tag v-if="endpoint.write" :value="t('mxapi_badge_write')" severity="warn" />
            <Tag v-if="endpoint.deprecated" :value="t('mxapi_badge_deprecated')" severity="danger" />
          </span>
          <code class="mxapi-path">{{ endpoint.public_path || endpoint.path }}</code>
          <span class="mxapi-title">{{ endpoint.title }}</span>
          <span class="mxapi-provider-tag">{{ endpoint.provider }}</span>
        </div>

        <EndpointDetails v-if="expanded[endpoint.id]" :endpoint="endpoint" :base-url="baseUrl" />
      </div>
    </div>
  </div>
</template>

<style scoped>
.mxapi-catalog {
  padding: 1rem;
}

.mxapi-mb {
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
  margin: 0.75rem 0;
}

.mxapi-muted {
  opacity: 0.7;
}

.mxapi-list {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.mxapi-item {
  border: 1px solid var(--p-content-border-color, #dee2e6);
  border-radius: 6px;
  overflow: hidden;
}

.mxapi-item-head {
  display: flex;
  gap: 0.75rem;
  align-items: center;
  padding: 0.6rem 0.75rem;
  cursor: pointer;
  flex-wrap: wrap;
}

.mxapi-item-open .mxapi-item-head {
  border-bottom: 1px solid var(--p-content-border-color, #dee2e6);
}

.mxapi-methods {
  display: flex;
  gap: 0.25rem;
  flex-wrap: wrap;
}

.mxapi-path {
  font-family: monospace;
}

.mxapi-title {
  flex: 1 1 auto;
}

.mxapi-provider-tag {
  opacity: 0.7;
  font-size: 0.85em;
}
</style>
