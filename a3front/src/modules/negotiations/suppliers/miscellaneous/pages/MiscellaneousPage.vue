<template>
  <div class="module-negotiations mt-4">
    <miscellaneous-filter-component />
    <miscellaneous-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import MiscellaneousListComponent from '@/modules/negotiations/suppliers/miscellaneous/components/miscellaneous-list-component.vue';
  import MiscellaneousFilterComponent from '@/modules/negotiations/suppliers/miscellaneous/components/miscellaneous-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
