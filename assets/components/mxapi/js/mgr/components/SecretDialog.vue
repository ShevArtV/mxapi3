<script setup>
import { computed, ref } from 'vue';
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
const copied = ref(false);

async function copy() {
  const text = props.data.client_secret;

  // navigator.clipboard доступен только на https и в свежих браузерах, а
  // менеджер часто открыт по http на стенде — поэтому fallback через поле.
  if (navigator.clipboard && window.isSecureContext) {
    await navigator.clipboard.writeText(text);
    done();
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
    done();
  } catch (e) {
    // Копировать нечем — секрет всё равно на экране, выделяется руками.
  }
  document.body.removeChild(area);
}

function done() {
  copied.value = true;
  toast.add({ severity: 'success', summary: t('mxapi_client_secret_copied'), life: 2000 });
}

function close() {
  copied.value = false;
  emit('close');
}
</script>

<template>
  <Dialog
    :visible="visible"
    modal
    :header="t('mxapi_client_secret_title')"
    :style="{ width: '38rem' }"
    :breakpoints="{ '40rem': '95vw' }"
    class="vueApp mxapi-app"
    @update:visible="close"
  >
    <Message severity="warn" :closable="false" class="mxapi-block">
      {{ t('mxapi_client_secret_warning') }}
    </Message>

    <p class="mxapi-label" id="mxapi-secret-key-label">client_id</p>
    <div class="mxapi-value" aria-labelledby="mxapi-secret-key-label">{{ data?.client_key }}</div>

    <p class="mxapi-label" id="mxapi-secret-value-label">client_secret</p>
    <div class="mxapi-value" aria-labelledby="mxapi-secret-value-label">{{ data?.client_secret }}</div>

    <Message v-if="data?.revoked_tokens" severity="info" :closable="false" class="mxapi-mt">
      {{ t('mxapi_client_tokens_revoked', { count: data.revoked_tokens }) }}
    </Message>

    <template #footer>
      <Button
        :label="copied ? t('mxapi_client_secret_copied') : t('mxapi_client_secret_copy')"
        :icon="copied ? 'pi pi-check' : 'pi pi-copy'"
        @click="copy"
      />
      <Button :label="t('mxapi_close')" severity="secondary" text @click="close" />
    </template>
  </Dialog>
</template>

<style scoped>
.mxapi-label {
  margin: 0.85rem 0 0.25rem;
  font-weight: 600;
}

.mxapi-value {
  padding: 0.6rem 0.75rem;
  border-radius: var(--p-content-border-radius, 6px);
  background: var(--p-surface-100);
  color: var(--p-text-color);
  font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
  word-break: break-all;
  /* Один клик выделяет значение целиком: секрет часто уносят руками, когда
     буфер обмена недоступен (менеджер по http). */
  user-select: all;
}

.mxapi-block {
  margin-bottom: 0.5rem;
}

.mxapi-mt {
  margin-top: 0.85rem;
}
</style>
