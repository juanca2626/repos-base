<template>
  <div class="module-negotiations ms-5 me-5 pb-5">
    <Can :I="PermissionActionEnum.READ" :a="ModulePermissionEnum.TAX_SUPPLIER_TYPE">
      <SupplierTaxFilterComponent @updateFiltersSupplierTax="updateFilters" />
      <SupplierTaxListComponent :filtersSupplierTax="filtersSupplierTax" />
    </Can>
  </div>
</template>

<script setup lang="ts">
  import SupplierTaxFilterComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxFilterComponent.vue';
  import SupplierTaxListComponent from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxListComponent.vue';
  import { useSupplierTax } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierTax';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const { filtersSupplierTax, updateFilters } = useSupplierTax();
</script>

<style scoped lang="scss"></style>
