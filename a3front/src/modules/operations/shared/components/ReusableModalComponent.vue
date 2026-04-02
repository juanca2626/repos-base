<template>
  <a-modal
    class="module-operations"
    :open="isVisible"
    :centered="centered"
    :closable="closable"
    :maskClosable="false"
    :width="width"
    @ok="onOk"
    @cancel="onCancel"
  >
    <!-- Header dinámico -->
    <template #title>
      <div class="custom-header">
        <h5>{{ title }}</h5>
        <div class="icon-close" @click="onCancel">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 31 30">
            <path
              d="M23 7.5L8 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8 7.5L23 22.5"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
      </div>
    </template>

    <!-- Contenido dinámico -->
    <slot name="content" />

    <!-- Footer dinámico -->
    <template #footer>
      <slot name="footer">
        <!-- Botones predeterminados -->
        <div class="default-footer">
          <a-button key="cancel" @click="onCancel" size="large" :disabled="loading"
            >Cancelar</a-button
          >
          <a-button key="submit" type="primary" :loading="loading" @click="onOk" size="large">
            Guardar
          </a-button>
        </div>
      </slot>
    </template>
  </a-modal>
</template>

<script lang="ts" setup>
  import { defineProps } from 'vue';
  import { useModalStore } from '@operations/shared/stores/modal.store'; // Importa el store

  // Desestructurar props
  defineProps({
    isVisible: { type: Boolean, required: true },
    title: { type: String, default: '' },
    loading: { type: Boolean, default: false },
    centered: { type: Boolean, default: true },
    closable: { type: Boolean, default: false },
    width: { type: Number, default: 488 },
    modalData: { type: Object, default: () => ({}) },
  });

  // Acceso al store
  const modalStore = useModalStore();

  const onOk = () => {
    // Llama directamente al método `handleModalOk` del store
    modalStore.handleModalOk();
  };

  const onCancel = () => {
    // Llama al método `handleModalCancel` del store
    modalStore.handleModalCancel();
  };
</script>

<style lang="scss" scoped>
  .custom-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .icon-close {
    cursor: pointer;
  }
  .default-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
  }
</style>
