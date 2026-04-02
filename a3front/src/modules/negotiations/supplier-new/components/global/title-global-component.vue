<template>
  <div v-if="isSupplierFormActive" class="title-global-component">
    {{ isEditMode ? 'Editar información del proveedor' : 'Registra un nuevo proveedor' }}
  </div>
  <HeaderGlobalComponent v-else />
</template>

<script setup lang="ts">
  import HeaderGlobalComponent from '@/modules/negotiations/supplier-new/components/global/header-global.component.vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'TitleGlobalComponent',
  });

  const { isEditMode, isSupplierFormActive } = useSupplierGlobalComposable();
</script>

<style scoped lang="scss">
  .title-global-component {
    font-weight: 700;
    font-size: 24px;
    line-height: 32px;
    color: #2f353a;
    margin-bottom: 16px;
  }
</style>
