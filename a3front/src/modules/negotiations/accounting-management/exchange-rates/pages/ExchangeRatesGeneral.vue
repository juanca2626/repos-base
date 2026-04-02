<template>
  <Can :I="PermissionActionEnum.READ" :a="ModulePermissionEnum.EXCHANGE_RATES">
    <ExchangeRatesFilterComponent @updateFilters="updateFilters"></ExchangeRatesFilterComponent>
    <ExchangeRatesListComponent :filters="filters" class="mt-4"></ExchangeRatesListComponent>
  </Can>
</template>
<script setup lang="ts">
  import ExchangeRatesFilterComponent from '@/modules/negotiations/accounting-management/exchange-rates/components/ExchangeRatesFilterComponent.vue';
  import ExchangeRatesListComponent from '@/modules/negotiations/accounting-management/exchange-rates/components/ExchangeRatesListComponent.vue';
  import { useExchangeRates } from '@/modules/negotiations/accounting-management/exchange-rates/composables/useExchangeRates';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const { filters, updateFilters } = useExchangeRates();
</script>
<style scoped lang="scss"></style>
