<template>
  <div class="module-negotiations mt-4">
    <train-filter-component />
    <train-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import TrainListComponent from '@/modules/negotiations/suppliers/trains/components/train-list-component.vue';
  import TrainFilterComponent from '@/modules/negotiations/suppliers/trains/components/train-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
