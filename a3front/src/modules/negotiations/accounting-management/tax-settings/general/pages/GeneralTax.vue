<template>
  <div class="module-negotiations ms-5 me-5 pb-5">
    <Can :I="PermissionActionEnum.READ" :a="ModulePermissionEnum.TAX_GENERAL">
      <FilterGeneralTaxComponent @updateFilters="updateFilters" />
      <ListGeneralTaxComponent :filters="filters" class="mt-4" />
    </Can>
  </div>
</template>

<script setup lang="ts">
  import FilterGeneralTaxComponent from '@/modules/negotiations/accounting-management/tax-settings/general/components/GeneralTaxFilterComponent.vue';
  import ListGeneralTaxComponent from '@/modules/negotiations/accounting-management/tax-settings/general/components/GeneralTaxListComponent.vue';
  import { useGeneralTax } from '@/modules/negotiations/accounting-management/tax-settings/general/composables/useGeneralTax';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const { filters, updateFilters } = useGeneralTax();
</script>
