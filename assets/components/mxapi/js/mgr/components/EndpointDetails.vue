<script setup>
import { computed } from 'vue';
import { t } from '../utils/i18n.js';

const props = defineProps({
  endpoint: { type: Object, required: true },
  baseUrl: { type: String, default: '' },
});

/**
 * Контекст, в котором выполняется эндпоинт. Права процессоров принадлежат
 * политике контекста, поэтому администратору это видеть так же важно, как право.
 */
const contextLabel = computed(() => {
  if (props.endpoint.modx_context === 'request') return t('mxapi_context_request');
  if (props.endpoint.modx_context) return props.endpoint.modx_context;
  return t('mxapi_context_default');
});

const rows = computed(() => {
  const e = props.endpoint;
  const list = [
    { label: t('mxapi_detail_id'), value: e.id, code: true },
    { label: t('mxapi_detail_url'), value: props.baseUrl + (e.public_path || e.path), code: true },
    { label: t('mxapi_detail_scope'), value: e.scope || t('mxapi_detail_not_required'), code: !!e.scope },
    {
      label: t('mxapi_detail_permission'),
      value: e.permission || t('mxapi_detail_not_required'),
      code: !!e.permission,
    },
    {
      label: t('mxapi_detail_auth'),
      value: e.auth === 'none' ? t('mxapi_detail_not_required') : t('mxapi_detail_bearer'),
    },
    { label: t('mxapi_detail_context'), value: contextLabel.value },
    { label: t('mxapi_detail_provider'), value: e.provider },
  ];

  // Шаблон роутера показываем только когда он отличается от читаемого адреса —
  // иначе в деталях две одинаковые строки.
  if (e.public_path && e.public_path !== e.path) {
    list.push({ label: t('mxapi_detail_route_template'), value: e.path, code: true });
  }
  if (e.processor) {
    list.push({ label: t('mxapi_detail_processor'), value: e.processor, code: true });
  }

  return list;
});

const curl = computed(() => {
  const e = props.endpoint;
  const url = props.baseUrl + e.path.replace(/\[|\]/g, '').replace(/\{(\w+)[^}]*\}/g, '{$1}');
  const method = (e.methods && e.methods[0]) || 'GET';

  let command = `curl -X ${method} '${url}'`;
  if (e.auth !== 'none') {
    command += " \\\n  -H 'Authorization: Bearer <token>'";
  }
  if (method !== 'GET') {
    command += " \\\n  -H 'Content-Type: application/json' \\\n  -d '{}'";
  }
  return command;
});
</script>

<template>
  <div class="mxapi-details">
    <p v-if="endpoint.description" class="mxapi-description">{{ endpoint.description }}</p>

    <table class="mxapi-meta">
      <tbody>
        <tr v-for="row in rows" :key="row.label">
          <th>{{ row.label }}</th>
          <td>
            <code v-if="row.code">{{ row.value }}</code>
            <span v-else>{{ row.value }}</span>
          </td>
        </tr>
      </tbody>
    </table>

    <p v-if="!endpoint.parameters || !endpoint.parameters.length" class="mxapi-muted">
      {{ t('mxapi_param_none') }}
    </p>
    <div v-else class="mxapi-table-scroll">
      <table class="mxapi-params">
        <thead>
          <tr>
            <th>{{ t('mxapi_param_name') }}</th>
            <th>{{ t('mxapi_param_type') }}</th>
            <th>{{ t('mxapi_param_in') }}</th>
            <th>{{ t('mxapi_param_required') }}</th>
            <th>{{ t('mxapi_param_description') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="parameter in endpoint.parameters" :key="parameter.name">
            <td><code>{{ parameter.name }}</code></td>
            <td>{{ parameter.type }}</td>
            <td>{{ parameter.in }}</td>
            <td>{{ parameter.required ? t('mxapi_yes') : t('mxapi_no') }}</td>
            <td>
              {{ parameter.description }}
              <div
                v-if="parameter.default !== null && parameter.default !== undefined"
                class="mxapi-muted"
              >
                {{ t('mxapi_param_default') }} <code>{{ String(parameter.default) }}</code>
              </div>
              <div v-if="parameter.enum && parameter.enum.length" class="mxapi-muted">
                {{ t('mxapi_param_enum') }} {{ parameter.enum.join(', ') }}
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <pre class="mxapi-curl">{{ curl }}</pre>
  </div>
</template>

<style scoped>
.mxapi-details {
  padding: 0.75rem;
}

.mxapi-description {
  margin: 0 0 0.75rem;
}

.mxapi-meta,
.mxapi-params {
  width: 100%;
  border-collapse: collapse;
  margin-bottom: 0.75rem;
}

.mxapi-meta th,
.mxapi-meta td,
.mxapi-params th,
.mxapi-params td {
  padding: 0.35rem 0.5rem;
  text-align: left;
  vertical-align: top;
  border-bottom: 1px solid var(--p-content-border-color, #dee2e6);
}

.mxapi-meta th {
  width: 14rem;
  font-weight: 600;
}

/* Широкая таблица параметров прокручивается внутри себя: страница менеджера
   не должна ехать по горизонтали. */
.mxapi-table-scroll {
  overflow-x: auto;
}

.mxapi-curl {
  padding: 0.6rem;
  border-radius: 6px;
  background: var(--p-content-hover-background, rgba(0, 0, 0, 0.05));
  overflow-x: auto;
  white-space: pre;
  margin: 0;
}

.mxapi-muted {
  opacity: 0.7;
}
</style>
