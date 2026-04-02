<template>
  <div class="module-negotiations mt-4">
    <restaurant-filter-component />
    <restaurant-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import RestaurantListComponent from '@/modules/negotiations/suppliers/restaurants/components/restaurant-list-component.vue';
  import RestaurantFilterComponent from '@/modules/negotiations/suppliers/restaurants/components/restaurant-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
