<template>
  <div class="module-negotiations mt-4">
    <cruise-filter-component />
    <cruise-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import CruiseListComponent from '@/modules/negotiations/suppliers/cruises/components/cruise-list-component.vue';
  import CruiseFilterComponent from '@/modules/negotiations/suppliers/cruises/components/cruise-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  defineOptions({
    name: 'CruisePage',
  });

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
