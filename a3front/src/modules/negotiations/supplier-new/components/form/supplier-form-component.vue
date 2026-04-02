<template>
  <div class="supplier-container-form-component">
    <div>
      <TitleGlobalComponent />
    </div>
    <!-- <div>
      <StepperGlobalComponent />
    </div> -->
    <div :key="`supplier-negotiations-wrapper-${supplierId || 'new'}`">
      <a-space direction="vertical">
        <SupplierNegotiationsByItemComponent />
      </a-space>
    </div>
  </div>
</template>

<script setup lang="ts">
  import SupplierNegotiationsByItemComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/supplier-negotiations-by-item-component.vue';
  import TitleGlobalComponent from '@/modules/negotiations/supplier-new/components/global/title-global-component.vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'SupplierFormComponent',
  });

  const { supplierId } = useSupplierGlobalComposable();
</script>

<style lang="scss">
  .supplier-container-form-component {
    margin: 20px;
    height: calc(100vh - 40px);
    overflow-y: auto;

    .ant-space {
      width: 100%;
    }
  }
</style>
