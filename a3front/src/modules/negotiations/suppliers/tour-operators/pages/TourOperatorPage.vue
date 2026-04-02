<template>
  <div class="module-negotiations mt-4">
    <tour-operator-filter-component />
    <tour-operator-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import TourOperatorListComponent from '@/modules/negotiations/suppliers/tour-operators/components/tour-operator-list-component.vue';
  import TourOperatorFilterComponent from '@/modules/negotiations/suppliers/tour-operators/components/tour-operator-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
