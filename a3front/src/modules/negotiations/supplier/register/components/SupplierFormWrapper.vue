<template>
  <a-form
    class="form-supplier"
    layout="vertical"
    ref="refSupplierForm"
    :model="formModel"
    :rules="rules"
  >
    <!-- Usar slot para insertar contenido personalizado -->
    <slot></slot>

    <!-- Botones de acción -->
    <div class="pb-3 text-info">* Los campos marcados son obligatorios.</div>
    <div class="form-buttons">
      <a-button class="btn-secondary ant-btn-md" @click="onCancel">Cancelar</a-button>
      <a-button type="primary" class="ant-btn-md" @click.prevent="handleSubmit">
        Guardar cambios
      </a-button>
    </div>
  </a-form>
</template>

<script setup lang="ts">
  import { defineProps, ref, onMounted, onUnmounted } from 'vue';
  import type { FormInstance } from 'ant-design-vue';
  import { off, on } from '@/modules/negotiations/api/eventBus';

  const props = defineProps({
    formModel: {
      type: Object,
      required: true,
    },
    rules: {
      type: Object,
      required: false,
    },
    onCancel: {
      type: Function,
      required: true,
    },
    saveFormHandler: {
      type: Function,
      required: true,
    },
  });

  const refSupplierForm = ref<FormInstance | null>(null);

  const handleSubmit = async () => {
    try {
      await refSupplierForm.value?.validate();
      props.saveFormHandler();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  const setupEventListeners = () => {
    on('submitSupplierForm', handleSubmit);
  };

  const cleanupEventListeners = () => {
    off('submitSupplierForm', handleSubmit);
  };

  onMounted(setupEventListeners);
  onUnmounted(cleanupEventListeners);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .text-info {
    text-align: right;
    margin-bottom: 2rem;
    color: $color-primary-strong;
    font-size: 14px;
    font-weight: 500;
  }
</style>
