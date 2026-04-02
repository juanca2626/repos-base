<template>
  <div class="module-negotiations pb-5">
    <supplier-classification-scrollable-tabs-component />
    <supplier-sub-classification-scrollable-tabs-component />
    <supplier-tax-assignments-filter-component />
    <supplier-tax-assignments-list-component />
  </div>
</template>
<script setup lang="ts">
  import SupplierClassificationScrollableTabsComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxAssignments/SupplierClassificationScrollableTabsComponent.vue';
  import SupplierSubClassificationScrollableTabsComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxAssignments/SupplierSubClassificationScrollableTabsComponent.vue';
  import SupplierTaxAssignmentsFilterComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxAssignments/SupplierTaxAssignmentsFilterComponent.vue';
  import SupplierTaxAssignmentsListComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxAssignments/SupplierTaxAssignmentsListComponent.vue';
  import { provide, ref } from 'vue';

  const isLoading = ref(false);

  provide('isLoading', isLoading);
</script>

<style scoped lang="scss"></style>
