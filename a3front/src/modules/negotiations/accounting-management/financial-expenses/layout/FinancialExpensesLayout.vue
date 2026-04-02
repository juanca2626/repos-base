<template>
  <div class="module-negotiations">
    <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.FINANCIAL_EXPENSES">
      <a-title-section
        title="Gastos Financieros"
        icon="file-invoice-dollar"
        :btn="{ title: 'Añadir', action: 'showDrawer', icon: 'plus' }"
        @handlerShowDrawer="handlerShowDrawer($event)"
      />
    </Can>

    <template v-if="canCreateOrUpdateForm">
      <FinancialExpensesFormComponent
        :showDrawer="showDrawer"
        @handlerShowDrawer="handlerShowDrawer($event)"
        @updateFilters="updateFilters"
      />
    </template>

    <div class="p-5">
      <router-view></router-view>
    </div>
  </div>
</template>
<script setup lang="ts">
  import ATitleSection from '@/components/backend/ATitleSection.vue';
  import FinancialExpensesFormComponent from '@/modules/negotiations/accounting-management/financial-expenses/components/FinancialExpensesFormComponent.vue';
  import { useFinancialExpensesLayout } from '@/modules/negotiations/accounting-management/financial-expenses/composables/useFinancialExpensesLayout';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';
  import { useModulePermission } from '@/composables/useModulePermission';

  const { showDrawer, handlerShowDrawer, updateFilters } = useFinancialExpensesLayout();

  const { canCreateOrUpdate } = useModulePermission();

  const canCreateOrUpdateForm = canCreateOrUpdate(ModulePermissionEnum.FINANCIAL_EXPENSES);
</script>
<style scoped lang="scss"></style>
