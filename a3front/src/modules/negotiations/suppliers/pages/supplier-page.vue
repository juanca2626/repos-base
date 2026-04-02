<template>
  <div class="mt-4">
    <supplier-filter-component />
    <supplier-list-component />
  </div>
</template>
<script setup lang="ts">
  import { onMounted } from 'vue';
  import SupplierListComponent from '@/modules/negotiations/suppliers/components/supplier-list-component.vue';
  import SupplierFilterComponent from '@/modules/negotiations/suppliers/components/supplier-filter-component.vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';

  const { initSupplierSubClassification } = useSupplier();

  onMounted(() => {
    initSupplierSubClassification();
  });
</script>
