<template>
  <a-modal
    :open="showModal"
    title="Resultados de registro de configuración"
    style="min-width: 700px"
    @cancel="handleCancel"
  >
    <a-table :columns="columns" :data-source="resultsByLocation" :pagination="false">
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'status'">
          <template v-if="record.errors.length > 0">
            <font-awesome-icon :icon="['fas', 'circle-xmark']" class="error-icon" />
          </template>
          <template v-else>
            <font-awesome-icon :icon="['fas', 'circle-check']" class="success-icon" />
          </template>
        </template>
        <template v-else-if="column.key === 'errors'">
          <p v-for="error in record.errors">
            {{ error }}
          </p>
        </template>
      </template>
    </a-table>
    <template #footer>
      <a-button key="back" @click="handleCancel">Cerrar</a-button>
    </template>
  </a-modal>
</template>

<script lang="ts" setup>
  import type { PropType } from 'vue';
  import type { ResultByLocation } from '@/modules/negotiations/type-unit-configurator/settings/interfaces';

  defineProps({
    showModal: {
      type: Boolean,
      required: true,
    },
    resultsByLocation: {
      type: Array as PropType<ResultByLocation[]>,
      required: true,
    },
  });

  const columns = [
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
    },
    {
      title: 'Lugar de operación',
      dataIndex: 'location',
      key: 'location',
    },
    {
      title: 'Mensaje',
      dataIndex: 'message',
      key: 'message',
    },
    {
      title: 'Errores',
      dataIndex: 'errors',
      key: 'errors',
    },
  ];

  const emit = defineEmits(['update:showModal']);

  const handleCancel = (): void => {
    emit('update:showModal', false);
  };
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .error-icon {
    font-size: 20px;
    color: $color-error;
  }

  .success-icon {
    font-size: 20px;
    color: $color-success;
  }
</style>
