<template>
  <div class="module-negotiations mt-4">
    <staff-filter-component />
    <staff-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import StaffListComponent from '@/modules/negotiations/suppliers/staff/components/staff-list-component.vue';
  import StaffFilterComponent from '@/modules/negotiations/suppliers/staff/components/staff-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
