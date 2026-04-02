<script setup lang="ts">
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import type { QuoteService } from '@/quotes/interfaces';
  import { computed, toRef } from 'vue';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  // const { quote } = useQuote();

  interface Props {
    showModal: boolean;
    service: QuoteService;
  }

  const props = defineProps<Props>();

  const showModal = toRef(props, 'showModal');
  const service = toRef(props, 'service');
  const type = toRef(service.value, 'type');

  const getServiceName = () => {
    if (type.value === 'service') {
      return service.value.service?.service_translations[0].name;
    } else {
      return service.value.hotel?.name;
    }
  };
  const serviceName = computed(() => getServiceName());

  interface Emits {
    (e: 'close'): void;

    (e: 'ok'): void;

    (e: 'cancel'): void;
  }

  const emits = defineEmits<Emits>();

  const onClose = () => {
    emits('close');
  };

  const onOk = () => {
    emits('ok');
  };

  const onCancel = () => {
    emits('cancel');
  };
</script>

<template>
  <ModalComponent :modal-active="showModal" class="modal-eliminarservicio" @close="onClose">
    <template #body>
      <h3 class="title">{{ t('quote.label.detele_service') }}</h3>
      <div class="description">
        {{ t('quote.label.detele_service_description') }}
        <b>{{ serviceName }}</b
        >. {{ t('quote.label.are_you_sure') }}
      </div>
    </template>
    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="onCancel">
          {{ t('quote.label.return') }}
        </button>
        <button :disabled="false" class="ok" @click="onOk">
          {{ t('quote.label.yes_continue') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<style scoped lang="scss"></style>
