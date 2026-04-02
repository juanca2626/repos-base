<template>
  <div class="module-negotiations mt-4">
    <water-transport-filter-component />
    <water-transport-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import WaterTransportListComponent from '@/modules/negotiations/suppliers/water-transports/components/water-transport-list-component.vue';
  import WaterTransportFilterComponent from '@/modules/negotiations/suppliers/water-transports/components/water-transport-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
