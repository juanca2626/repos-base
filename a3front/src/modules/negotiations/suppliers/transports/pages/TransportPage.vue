<template>
  <div class="module-negotiations mt-4">
    <transport-filter-component />
    <transport-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import TransportListComponent from '@/modules/negotiations/suppliers/transports/components/transport-list-component.vue';
  import TransportFilterComponent from '@/modules/negotiations/suppliers/transports/components/transport-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
