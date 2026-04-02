<template>
  <div class="module-negotiations">
    <Can :I="PermissionActionEnum.READ" :a="ModulePermissionEnum.COST_SALE_ACCOUNTS">
      <CostSaleAccountsFilterComponent
        @updateFilters="updateFilters"
      ></CostSaleAccountsFilterComponent>
      <CostSaleAccountsListComponent
        :filters="filters"
        class="mt-4"
      ></CostSaleAccountsListComponent>
    </Can>
  </div>
</template>
<script setup lang="ts">
  import CostSaleAccountsFilterComponent from '@/modules/negotiations/accounting-management/cost-sale-accounts/components/CostSaleAccountsFilterComponent.vue';
  import CostSaleAccountsListComponent from '@/modules/negotiations/accounting-management/cost-sale-accounts/components/CostSaleAccountsListComponent.vue';
  import { useCostSaleAccounts } from '@/modules/negotiations/accounting-management/cost-sale-accounts/composables/useCostSaleAccounts';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const { filters, updateFilters } = useCostSaleAccounts();
</script>

<style scoped lang="scss"></style>
