<template>
  <div class="module-negotiations">
    <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.EXCHANGE_RATES">
      <a-title-section
        title="Tipo de cambio estimado"
        icon="dollar-sign"
        :btn="{ title: 'Añadir', action: 'showDrawer', icon: 'plus' }"
        @handlerShowDrawer="handlerShowDrawer($event)"
      />
    </Can>

    <template v-if="canCreateOrUpdate">
      <ExchangeRatesFormComponent
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
  import { useAbility } from '@casl/vue';
  import { computed } from 'vue';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';
  import { useExchangeRatesLayout } from '@/modules/negotiations/accounting-management/exchange-rates/composables/useExchangeRatesLayout';
  import ATitleSection from '@/components/backend/ATitleSection.vue';
  import ExchangeRatesFormComponent from '@/modules/negotiations/accounting-management/exchange-rates/components/ExchangeRatesFormComponent.vue';

  const { showDrawer, handlerShowDrawer, updateFilters } = useExchangeRatesLayout();

  const ability = useAbility();

  const canCreateOrUpdate = computed(() => {
    return (
      ability.can(PermissionActionEnum.CREATE, ModulePermissionEnum.EXCHANGE_RATES) ||
      ability.can(PermissionActionEnum.UPDATE, ModulePermissionEnum.EXCHANGE_RATES)
    );
  });
</script>

<style scoped lang="scss"></style>
