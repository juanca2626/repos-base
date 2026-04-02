<template>
  <div class="module-negotiations mt-4">
    <tourist-attraction-filter-component />
    <tourist-attraction-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import TouristAttractionListComponent from '@/modules/negotiations/suppliers/tourist-attractions/components/tourist-attraction-list-component.vue';
  import TouristAttractionFilterComponent from '@/modules/negotiations/suppliers/tourist-attractions/components/tourist-attraction-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
