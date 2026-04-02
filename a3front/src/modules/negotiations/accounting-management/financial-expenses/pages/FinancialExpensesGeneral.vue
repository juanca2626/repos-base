<template>
  <Can :I="PermissionActionEnum.READ" :a="ModulePermissionEnum.FINANCIAL_EXPENSES">
    <FinancialExpensesFilterComponent
      @updateFilters="updateFilters"
    ></FinancialExpensesFilterComponent>
    <FinancialExpensesListComponent
      :filters="filters"
      class="mt-4"
    ></FinancialExpensesListComponent>
  </Can>
</template>

<script setup lang="ts">
  import FinancialExpensesFilterComponent from '@/modules/negotiations/accounting-management/financial-expenses/components/FinancialExpensesFilterComponent.vue';
  import FinancialExpensesListComponent from '@/modules/negotiations/accounting-management/financial-expenses/components/FinancialExpensesListComponent.vue';
  import { useFinancialExpenses } from '@/modules/negotiations/accounting-management/financial-expenses/composables/useFinancialExpenses';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const { filters, updateFilters } = useFinancialExpenses();
</script>

<style scoped lang="scss"></style>
