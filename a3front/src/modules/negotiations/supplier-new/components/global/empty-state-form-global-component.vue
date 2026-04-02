<template>
  <div class="empty-state-form-component">
    <div class="title-empty-state">{{ title }}</div>
    <div class="information-pending">Información pendiente.</div>
    <div class="add-info" @click="handleShowFormSpecific(formSpecific, true)">
      Agregar información
    </div>
  </div>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'EmptyStateFormGlobalComponent',
  });

  defineProps({
    title: {
      type: String,
      required: true,
    },
    formSpecific: {
      type: String,
      required: true,
    },
  });

  const { handleShowFormSpecific } = useSupplierGlobalComposable();
</script>

<style scoped lang="scss">
  .empty-state-form-component {
    display: grid;
    row-gap: 0.5rem;
    width: max-content;

    .title-empty-state {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #7e8285;
      width: max-content;
    }

    .information-pending {
      font-weight: 400;
      font-size: 14px;
      line-height: 20px;
      vertical-align: middle;
      color: #7e8285;
      width: max-content;
    }

    .add-info {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      vertical-align: middle;
      color: #1284ed;
      cursor: pointer;
      width: max-content;

      &.disabled {
        color: #ccc;
        cursor: not-allowed;
      }
    }
  }
</style>
