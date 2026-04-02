<template>
  <a-modal
    v-model:open="flagModalEmails"
    :destroyOnClose="true"
    :width="720"
    :closable="true"
    :maskClosable="false"
    @cancel="closeModalEmails"
  >
    <template #title>
      <span class="text-center">Correos adicionales para solicitud de reserva:</span>
    </template>

    <a-form layout="vertical">
      <a-form-item label="Agregar correos adicionales">
        <a-select
          v-model:value="internalEmails"
          mode="tags"
          style="width: 100%"
          placeholder="Agregar correos adicionales"
          :options="[]"
        />
      </a-form-item>
    </a-form>

    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button type="default" size="large" class="text-600" @click="closeModalEmails">
            {{ t('global.button.cancel') }}
          </a-button>
          <a-button type="primary" size="large" class="text-600" @click="handleChangeEmails">
            {{ t('global.button.save') }}
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-modal>
</template>

<script setup>
  import { ref, computed } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({ useScope: 'global' });

  const props = defineProps({
    modelValue: {
      type: Array,
      default: () => [],
    },
  });

  const emit = defineEmits(['update:modelValue', 'close', 'save']);

  const flagModalEmails = ref(true);

  // Computed para v-model de emails
  const internalEmails = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
  });

  const closeModalEmails = () => {
    flagModalEmails.value = false;
    emit('close');
  };

  const handleChangeEmails = () => {
    closeModalEmails();
    emit('save');
  };
</script>
