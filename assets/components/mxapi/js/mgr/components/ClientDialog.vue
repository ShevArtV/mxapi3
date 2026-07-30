<script setup>
import { computed, ref, watch } from 'vue';
import { Button, Checkbox, Dialog, InputText, Message, Select } from 'primevue';
import { ClientApi, errorMessage } from '../api/connector.js';
import { t } from '../utils/i18n.js';

const props = defineProps({
  visible: { type: Boolean, default: false },
  client: { type: Object, default: null },
  userId: { type: Number, required: true },
});
const emit = defineEmits(['update:visible', 'saved']);

// Значения token_ttl совпадают с ClientRecord: 0 — общий TTL сайта,
// -1 — бессрочно, положительное число — секунды.
const TTL_SITE = 0;
const TTL_NEVER = -1;
const TTL_CUSTOM = 'custom';
const TTL_MIN = 60;

const name = ref('');
const ttlMode = ref(TTL_SITE);
const ttlSeconds = ref('3600');
const selected = ref([]);
const groups = ref([]);
const totalScopes = ref(0);
const totalExisting = ref(0);
const error = ref('');
const saving = ref(false);
const loadingScopes = ref(false);
const touched = ref(false);

const isEdit = computed(() => !!props.client);

const ttlOptions = computed(() => [
  { label: t('mxapi_client_ttl_site'), value: TTL_SITE },
  { label: t('mxapi_client_ttl_custom'), value: TTL_CUSTOM },
  { label: t('mxapi_client_ttl_never'), value: TTL_NEVER },
]);

// Пустой список scope почти всегда означает не «эндпоинтов нет», а что
// пользователю не выдан доступ к namespace mxapi — сообщения разные.
const emptyMessage = computed(() => {
  if (totalExisting.value === 0) return t('mxapi_client_scopes_none_exist');
  if (totalScopes.value === 0) return t('mxapi_client_scopes_none_allowed');
  return '';
});

const ttlInvalid = computed(() => {
  if (ttlMode.value !== TTL_CUSTOM) return false;
  const value = parseInt(ttlSeconds.value, 10);
  return !Number.isFinite(value) || value < TTL_MIN;
});

// Кнопка «Сохранить» гаснет ровно на тех условиях, которые отвергнет сервер:
// проверка на клиенте не заменяет серверную, а избавляет от лишнего рейса.
const canSave = computed(
  () => name.value.trim() !== '' && selected.value.length > 0 && !ttlInvalid.value
);

async function loadScopes() {
  loadingScopes.value = true;
  try {
    const res = await ClientApi.scopes(props.userId);
    const data = res.object || res;
    groups.value = data.groups || [];
    totalScopes.value = data.total_scopes || 0;
    totalExisting.value = data.total_existing || 0;
  } catch (e) {
    error.value = errorMessage(e, '');
  } finally {
    loadingScopes.value = false;
  }
}

function reset() {
  error.value = '';
  touched.value = false;
  if (props.client) {
    name.value = props.client.name || '';
    selected.value = [...(props.client.scopes || [])];
    const ttl = Number(props.client.token_ttl || 0);
    if (ttl === TTL_NEVER) {
      ttlMode.value = TTL_NEVER;
    } else if (ttl > 0) {
      ttlMode.value = TTL_CUSTOM;
      ttlSeconds.value = String(ttl);
    } else {
      ttlMode.value = TTL_SITE;
    }
  } else {
    name.value = '';
    selected.value = [];
    ttlMode.value = TTL_SITE;
    ttlSeconds.value = '3600';
  }
}

watch(
  () => props.visible,
  (visible) => {
    if (visible) {
      reset();
      loadScopes();
    }
  }
);

function groupScopes(group) {
  return group.scopes.map((s) => s.scope);
}

function groupState(group) {
  const scopes = groupScopes(group);
  const checked = scopes.filter((s) => selected.value.includes(s));
  return { all: checked.length === scopes.length && scopes.length > 0, some: checked.length > 0 };
}

function toggleGroup(group) {
  const scopes = groupScopes(group);
  if (groupState(group).all) {
    selected.value = selected.value.filter((s) => !scopes.includes(s));
  } else {
    selected.value = [...new Set([...selected.value, ...scopes])];
  }
}

function ttlValue() {
  if (ttlMode.value === TTL_NEVER) return TTL_NEVER;
  if (ttlMode.value === TTL_CUSTOM) return parseInt(ttlSeconds.value, 10) || 0;
  return TTL_SITE;
}

async function save() {
  touched.value = true;
  if (!canSave.value) return;

  error.value = '';
  saving.value = true;
  try {
    const payload = { name: name.value.trim(), scopes: selected.value, token_ttl: ttlValue() };
    const res = isEdit.value
      ? await ClientApi.update(props.userId, props.client.id, payload)
      : await ClientApi.create(props.userId, payload);
    emit('saved', res.object || res);
  } catch (e) {
    error.value = errorMessage(e, t('mxapi_client_err_save'));
  } finally {
    saving.value = false;
  }
}
</script>

<template>
  <Dialog
    :visible="visible"
    modal
    :header="isEdit ? t('mxapi_client_window_update') : t('mxapi_client_window_create')"
    :style="{ width: '46rem' }"
    :breakpoints="{ '48rem': '95vw' }"
    class="vueApp mxapi-app"
    @update:visible="emit('update:visible', $event)"
  >
    <Message v-if="error" severity="error" :closable="false" class="mxapi-block">{{ error }}</Message>

    <div class="mxapi-field">
      <label for="mxapi-client-name">{{ t('mxapi_client_name') }} <span aria-hidden="true">*</span></label>
      <InputText
        id="mxapi-client-name"
        v-model="name"
        class="mxapi-w-full"
        :invalid="touched && name.trim() === ''"
        required
        @blur="touched = true"
      />
      <small v-if="touched && name.trim() === ''" class="mxapi-error-text">
        {{ t('mxapi_client_err_name_ns') }}
      </small>
    </div>

    <div class="mxapi-field">
      <label for="mxapi-client-ttl">{{ t('mxapi_client_ttl') }}</label>
      <div class="mxapi-ttl">
        <Select
          id="mxapi-client-ttl"
          v-model="ttlMode"
          :options="ttlOptions"
          option-label="label"
          option-value="value"
        />
        <InputText
          v-if="ttlMode === 'custom'"
          v-model="ttlSeconds"
          class="mxapi-ttl-input"
          inputmode="numeric"
          :invalid="ttlInvalid"
          :aria-label="t('mxapi_client_ttl_seconds')"
        />
        <span v-if="ttlMode === 'custom'" class="mxapi-muted">{{ t('mxapi_client_ttl_sec_short') }}</span>
      </div>
      <small v-if="ttlInvalid" class="mxapi-error-text">{{ t('mxapi_client_err_ttl') }}</small>
      <Message v-if="ttlMode === -1" severity="warn" :closable="false" class="mxapi-mt">
        {{ t('mxapi_client_ttl_never_warning') }}
      </Message>
    </div>

    <fieldset class="mxapi-field mxapi-fieldset">
      <legend>{{ t('mxapi_client_scopes') }} <span aria-hidden="true">*</span></legend>
      <p class="mxapi-muted mxapi-caption">{{ t('mxapi_client_scopes_caption') }}</p>

      <div v-if="loadingScopes" class="mxapi-scopes-loading">
        <div v-for="n in 3" :key="n" class="mxapi-skeleton mxapi-skeleton-row"></div>
      </div>
      <Message v-else-if="emptyMessage" severity="warn" :closable="false">{{ emptyMessage }}</Message>

      <div v-else class="mxapi-scopes">
        <div v-for="group in groups" :key="group.provider" class="mxapi-scope-group">
          <div class="mxapi-scope-group-head">
            <Checkbox
              :model-value="groupState(group).all"
              binary
              :indeterminate="groupState(group).some && !groupState(group).all"
              :input-id="`group-${group.provider}`"
              @update:model-value="toggleGroup(group)"
            />
            <label :for="`group-${group.provider}`"><strong>{{ group.provider }}</strong></label>
          </div>

          <div v-for="row in group.scopes" :key="row.scope" class="mxapi-scope-row">
            <Checkbox v-model="selected" :value="row.scope" :input-id="`scope-${row.scope}`" />
            <label :for="`scope-${row.scope}`">
              <code>{{ row.scope }}</code>
              <span v-if="row.permission" class="mxapi-muted"> · {{ row.permission }}</span>
              <span class="mxapi-muted mxapi-scope-endpoints">{{ row.endpoints_text }}</span>
            </label>
          </div>
        </div>
      </div>

      <small v-if="touched && !selected.length && !emptyMessage" class="mxapi-error-text">
        {{ t('mxapi_client_err_scopes_ns') }}
      </small>
    </fieldset>

    <template #footer>
      <Button :label="t('mxapi_cancel')" severity="secondary" text @click="emit('update:visible', false)" />
      <Button :label="t('mxapi_save')" :loading="saving" :disabled="!canSave" @click="save" />
    </template>
  </Dialog>
</template>

<style scoped>
.mxapi-field {
  margin-bottom: 1.25rem;
}

.mxapi-field > label,
.mxapi-fieldset > legend {
  display: block;
  font-weight: 600;
  margin-bottom: 0.35rem;
  padding: 0;
}

.mxapi-fieldset {
  border: 0;
  margin-inline: 0;
  padding: 0;
}

.mxapi-w-full {
  width: 100%;
}

.mxapi-ttl {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  flex-wrap: wrap;
}

.mxapi-ttl-input {
  width: 8rem;
}

.mxapi-caption {
  margin: 0 0 0.5rem;
  max-width: 70ch;
}

.mxapi-error-text {
  display: block;
  margin-top: 0.3rem;
  color: var(--p-message-error-color, var(--p-red-600));
}

.mxapi-scopes,
.mxapi-scopes-loading {
  max-height: 22rem;
  overflow-y: auto;
  border: 1px solid var(--p-content-border-color);
  border-radius: var(--p-content-border-radius, 6px);
  padding: 0.75rem;
}

.mxapi-skeleton-row + .mxapi-skeleton-row {
  margin-top: 0.5rem;
}

.mxapi-scope-group + .mxapi-scope-group {
  margin-top: 1rem;
}

.mxapi-scope-group-head {
  display: flex;
  gap: 0.5rem;
  align-items: center;
  margin-bottom: 0.35rem;
}

.mxapi-scope-row {
  display: flex;
  gap: 0.5rem;
  align-items: flex-start;
  padding: 0.3rem 0 0.3rem 1.5rem;
}

.mxapi-scope-row label {
  cursor: pointer;
}

.mxapi-scope-endpoints {
  display: block;
  font-size: 0.85em;
}

.mxapi-block {
  margin-bottom: 0.75rem;
}

.mxapi-mt {
  margin-top: 0.5rem;
}
</style>
