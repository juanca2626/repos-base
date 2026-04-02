<template>
  <div class="module-negotiations mt-4">
    <local-operator-filter-component />
    <local-operator-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import LocalOperatorListComponent from '@/modules/negotiations/suppliers/local-operators/components/local-operator-list-component.vue';
  import LocalOperatorFilterComponent from '@/modules/negotiations/suppliers/local-operators/components/local-operator-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  defineOptions({
    name: 'LocalOperatorPage',
  });

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
