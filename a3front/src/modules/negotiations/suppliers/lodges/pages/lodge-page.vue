<template>
  <div class="module-negotiations mt-4">
    <lodge-filter-component />
    <lodge-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import LodgeListComponent from '@/modules/negotiations/suppliers/lodges/components/lodge-list-component.vue';
  import LodgeFilterComponent from '@/modules/negotiations/suppliers/lodges/components/lodge-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
