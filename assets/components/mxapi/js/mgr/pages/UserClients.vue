<script setup>
import { onMounted, ref } from 'vue';
import { Button, Column, ConfirmPopup, DataTable, Message, Tag, Toast, useConfirm, useToast } from 'primevue';
import { ClientApi, config, errorMessage, listOf } from '../api/connector.js';
import { t } from '../utils/i18n.js';
import ClientDialog from '../components/ClientDialog.vue';
import SecretDialog from '../components/SecretDialog.vue';

const userId = config().user_id;

const clients = ref([]);
const loading = ref(true);
const error = ref('');
// Идёт ли действие по конкретной строке: без этого повторный клик уходит в
// сервер дважды, а пользователь не понимает, приняли ли команду.
const busyId = ref(0);

const dialogVisible = ref(false);
const dialogClient = ref(null);
const secret = ref(null);

const toast = useToast();
const confirm = useConfirm();

async function load() {
  loading.value = true;
  error.value = '';
  try {
    clients.value = listOf(await ClientApi.getList(userId));
  } catch (e) {
    error.value = errorMessage(e, t('mxapi_client_err_load'));
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  dialogClient.value = null;
  dialogVisible.value = true;
}

function openEdit(client) {
  dialogClient.value = client;
  dialogVisible.value = true;
}

// Секрет существует только в ответе на создание и перевыпуск: в базе лежит хэш,
// показать его повторно невозможно.
function onSaved(result) {
  dialogVisible.value = false;
  if (result && result.client_secret) {
    secret.value = result;
  } else {
    toast.add({ severity: 'success', summary: t('mxapi_client_saved'), life: 2500 });
  }
  load();
}

async function toggleActive(client) {
  busyId.value = client.id;
  try {
    await ClientApi.update(userId, client.id, { active: !client.active });
    await load();
  } catch (e) {
    toast.add({ severity: 'error', summary: errorMessage(e, t('mxapi_client_err_save')), life: 5000 });
  } finally {
    busyId.value = 0;
  }
}

function askRemove(event, client) {
  confirm.require({
    target: event.currentTarget,
    message: t('mxapi_client_remove_confirm'),
    acceptLabel: t('mxapi_client_remove'),
    rejectLabel: t('mxapi_cancel'),
    acceptProps: { severity: 'danger' },
    accept: async () => {
      busyId.value = client.id;
      try {
        await ClientApi.remove(userId, client.id);
        await load();
        toast.add({ severity: 'success', summary: t('mxapi_client_removed'), life: 2500 });
      } catch (e) {
        toast.add({ severity: 'error', summary: errorMessage(e, t('mxapi_client_err_remove')), life: 5000 });
      } finally {
        busyId.value = 0;
      }
    },
  });
}

function askRegenerate(event, client) {
  confirm.require({
    target: event.currentTarget,
    message: t('mxapi_client_regenerate_confirm'),
    acceptLabel: t('mxapi_client_regenerate_short'),
    rejectLabel: t('mxapi_cancel'),
    accept: async () => {
      busyId.value = client.id;
      try {
        // Плановая ротация секрета не гасит выданные токены — отзыв идёт
        // отдельным действием, чтобы не ронять работающую интеграцию.
        const res = await ClientApi.regenerate(userId, client.id, false);
        secret.value = res.object || res;
        await load();
      } catch (e) {
        toast.add({ severity: 'error', summary: errorMessage(e, t('mxapi_client_err_save')), life: 5000 });
      } finally {
        busyId.value = 0;
      }
    },
  });
}

function ttlLabel(client) {
  if (client.token_ttl === -1) return t('mxapi_client_ttl_never');
  if (!client.token_ttl) return t('mxapi_client_ttl_site');
  return `${client.token_ttl} ${t('mxapi_client_ttl_sec_short')}`;
}

onMounted(load);
</script>

<template>
  <div class="mxapi-app mxapi-clients">
    <Toast />
    <ConfirmPopup />

    <div class="mxapi-head">
      <p class="mxapi-intro mxapi-muted">{{ t('mxapi_client_intro') }}</p>
      <Button :label="t('mxapi_client_create')" icon="pi pi-plus" @click="openCreate" />
    </div>

    <Message v-if="error" severity="error" :closable="false" class="mxapi-block">{{ error }}</Message>

    <!-- Своя прокрутка: внутри вкладки пользователя таблица шире колонки, и без
         неё колонка действий обрезается краем панели. -->
    <div class="mxapi-grid-scroll">
      <DataTable :value="clients" :loading="loading" data-key="id" size="small">
        <template #empty>
          <div class="mxapi-empty">
            <p class="mxapi-empty-title">{{ t('mxapi_client_empty') }}</p>
            <p class="mxapi-muted">{{ t('mxapi_client_empty_hint') }}</p>
          </div>
        </template>

        <Column field="name" :header="t('mxapi_client_name')" />
        <Column field="client_key" :header="t('mxapi_client_key')">
          <template #body="{ data }"><code>{{ data.client_key }}</code></template>
        </Column>
        <Column field="scopes_text" :header="t('mxapi_client_scopes')" />
        <Column :header="t('mxapi_client_ttl')">
          <template #body="{ data }">{{ ttlLabel(data) }}</template>
        </Column>
        <Column field="tokens_active" :header="t('mxapi_client_tokens')" />
        <Column field="createdon_text" :header="t('mxapi_client_createdon')" />
        <Column :header="t('mxapi_client_status')">
          <template #body="{ data }">
            <Tag
              :value="data.active ? t('mxapi_client_active') : t('mxapi_client_inactive')"
              :severity="data.active ? 'success' : 'secondary'"
            />
          </template>
        </Column>
        <Column :header="t('mxapi_client_actions')">
          <template #body="{ data }">
            <div class="mxapi-row-actions">
              <Button
                icon="pi pi-pencil"
                severity="secondary"
                text
                :disabled="busyId === data.id"
                :aria-label="t('mxapi_client_edit')"
                :title="t('mxapi_client_edit')"
                @click="openEdit(data)"
              />
              <Button
                icon="pi pi-refresh"
                severity="secondary"
                text
                :disabled="busyId === data.id"
                :aria-label="t('mxapi_client_regenerate')"
                :title="t('mxapi_client_regenerate')"
                @click="askRegenerate($event, data)"
              />
              <Button
                :icon="data.active ? 'pi pi-pause' : 'pi pi-play'"
                severity="secondary"
                text
                :loading="busyId === data.id"
                :aria-label="data.active ? t('mxapi_client_deactivate') : t('mxapi_client_activate')"
                :title="data.active ? t('mxapi_client_deactivate') : t('mxapi_client_activate')"
                @click="toggleActive(data)"
              />
              <Button
                icon="pi pi-trash"
                severity="danger"
                text
                :disabled="busyId === data.id"
                :aria-label="t('mxapi_client_remove')"
                :title="t('mxapi_client_remove')"
                @click="askRemove($event, data)"
              />
            </div>
          </template>
        </Column>
      </DataTable>
    </div>

    <ClientDialog
      v-model:visible="dialogVisible"
      :client="dialogClient"
      :user-id="userId"
      @saved="onSaved"
    />

    <SecretDialog :data="secret" @close="secret = null" />
  </div>
</template>

<style scoped>
.mxapi-clients {
  padding: 0.5rem 0;
}

.mxapi-head {
  display: flex;
  gap: 1rem;
  align-items: flex-start;
  justify-content: space-between;
  flex-wrap: wrap;
  margin-bottom: 0.75rem;
}

.mxapi-intro {
  margin: 0;
  max-width: 75ch;
}

.mxapi-block {
  margin-bottom: 0.75rem;
}

.mxapi-row-actions {
  display: flex;
  gap: 0.15rem;
}

.mxapi-grid-scroll {
  overflow-x: auto;
  max-width: 100%;
}

.mxapi-empty {
  padding: 1.5rem 0.5rem;
  text-align: center;
}

.mxapi-empty-title {
  margin: 0 0 0.25rem;
  font-weight: 600;
}

.mxapi-empty p {
  margin: 0;
}
</style>
