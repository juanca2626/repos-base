<template>
  <ModuleTreasuryBankInformationComponent
    v-if="activeSideBarOptionKey !== FormComponentEnum.MODULE_TREASURY_ACCOUNT_CONTABLE"
  />
  <ModuleTreasuryBeneficiariesComponent
    v-if="activeSideBarOptionKey !== FormComponentEnum.MODULE_TREASURY_ACCOUNT_CONTABLE"
  />
  <ModuleTreasuryAccountContableComponent
    v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_TREASURY_ACCOUNT_CONTABLE"
  />
</template>
<script setup lang="ts">
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
  import ModuleTreasuryBankInformationComponent from '@/modules/negotiations/supplier-new/components/form/treasury/form/module-configuration/module-treasury-bank-information-component.vue';
  import ModuleTreasuryBeneficiariesComponent from '@/modules/negotiations/supplier-new/components/form/treasury/form/module-configuration/module-treasury-beneficiaries-component.vue';
  import ModuleTreasuryAccountContableComponent from '@/modules/negotiations/supplier-new/components/form/treasury/form/module-configuration/module-treasury-account-contable-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

  defineOptions({
    name: 'RenderModuleConfigurationTreasuryComponent',
  });
  const { activeSideBarOptionKey } = useSupplierGlobalComposable();
</script>
