<script setup>
import { computed } from 'vue';
import { Button, Dialog, Message, useToast } from 'primevue';
import { t } from '../utils/i18n.js';

/**
 * Секрет существует только здесь: в базе лежит хэш, повторно показать его
 * невозможно. Поэтому окно модальное, с явным предупреждением и копированием в
 * один клик — закрыть его не скопировав означает перевыпуск.
 */
const props = defineProps({
  data: { type: Object, default: null },
});
const emit = defineEmits(['close']);

const toast = useToast();
const visible = computed(() => !!props.data);

async function copy() {
  const text = props.data.client_secret;

  // navigator.clipboard доступен только на https и в свежих браузерах, а
  // менеджер часто открыт по http на стенде — поэтому fallback через поле.
  if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(text);
    toast.add({ severity: 'success', summary: t('mxapi_client_secret_copied'), life: 2000 });
    return;
  }

  const area = document.createElement('textarea');
  area.value = text;
  area.style.position = 'fixed';
  area.style.opacity = '0';
  document.body.appendChild(area);
  area.select();
  try {
    document.execCommand('copy');
    toast.add({ severity: 'success', summary: t('mxapi_client_secret_copied'), life: 2000 });
  } catch (e) {
    // Копировать нечем — секрет всё равно на экране, выделяется руками.
  }
  document.body.removeChild(area);
}
</script>

<template>
  <Dialog
    :visible="visible"
    modal
    :header="t('mxapi_client_secret_title')"
    :style="{ width: '38rem' }"
    class="vueApp"
    @update:visible="emit('close')"
  >
    <Message severity="warn" :closable="false" class="mxapi-mb">
      {{ t('mxapi_client_secret_warning') }}
    </Message>

    <p class="mxapi-label">client_id</p>
    <div class="mxapi-value">{{ data?.client_key }}</div>

    <p class="mxapi-label">client_secret</p>
    <div class="mxapi-value">{{ data?.client_secret }}</div>

    <Message
      v-if="data?.revoked_tokens"
      severity="info"
      :closable="false"
      class="mxapi-mt"
    >
      {{ t('mxapi_client_tokens_revoked', { count: data.revoked_tokens }) }}
    </Message>

    <template #footer>
      <Button :label="t('mxapi_client_secret_copy')" icon="pi pi-copy" @click="copy" />
      <Button :label="t('mxapi_close')" severity="secondary" text @click="emit('close')" />
    </template>
  </Dialog>
</template>

<style scoped>
.mxapi-label {
  margin: 0.75rem 0 0.25rem;
  font-weight: 600;
}

.mxapi-value {
  padding: 0.5rem;
  border-radius: 6px;
  font-family: monospace;
  word-break: break-all;
  background: var(--p-content-hover-background, rgba(0, 0, 0, 0.05));
}

.mxapi-mb {
  margin-bottom: 0.5rem;
}

.mxapi-mt {
  margin-top: 0.75rem;
}
</style>
