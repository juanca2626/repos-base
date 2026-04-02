<template>
  <div class="module-negotiations">
    <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.COST_SALE_ACCOUNTS">
      <a-title-section
        title="Cuentas costo y venta por clasificación"
        icon="dollar-sign"
        :btn="{ title: 'Añadir', action: 'showDrawer', icon: 'plus' }"
        @handlerShowDrawer="handlerShowDrawer($event)"
      />
    </Can>

    <template v-if="canCreateOrUpdateForm">
      <CostSaleAccountsFormComponent
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
  import CostSaleAccountsFormComponent from '@/modules/negotiations/accounting-management/cost-sale-accounts/components/CostSaleAccountsFormComponent.vue';
  import { useCostSaleAccountsLayout } from '@/modules/negotiations/accounting-management/cost-sale-accounts/composables/useCostSaleAccountsLayout';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';
  import { useModulePermission } from '@/composables/useModulePermission';

  const { showDrawer, handlerShowDrawer, updateFilters } = useCostSaleAccountsLayout();

  const { canCreateOrUpdate } = useModulePermission();

  const canCreateOrUpdateForm = canCreateOrUpdate(ModulePermissionEnum.COST_SALE_ACCOUNTS);
</script>
<style scoped lang="scss"></style>
