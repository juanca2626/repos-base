<template>
  <div class="module-negotiations mt-4">
    <airline-filter-component />
    <airline-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import AirlineFilterComponent from '@/modules/negotiations/suppliers/airlines/components/airline-filter-component.vue';
  import AirlineListComponent from '@/modules/negotiations/suppliers/airlines/components/airline-list-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
