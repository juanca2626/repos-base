<template>
  <ModuleServicesComponent v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_SERVICES" />
  <ModuleListUnitsComponent v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_LIST_UNITS" />
  <ModulePolicyComponent v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_POLICIES" />
  <ModuleContactsComponent v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_CONTACT" />
  <ModuleDataSheetComponent v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_DATA_SHEET" />
  <ModuleNegotiationsInformationSunatComponent
    v-if="activeSideBarOptionKey === FormComponentEnum.MODULE_SUNAT_INFORMATION"
  />
</template>

<script setup lang="ts">
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
  import ModuleServicesComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-services-component.vue';
  import ModulePolicyComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-policy-component.vue';
  import ModuleContactsComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-contacts-component.vue';
  import ModuleNegotiationsInformationSunatComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-negotiations-information-sunat-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import ModuleListUnitsComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-list-units-component.vue';
  import ModuleDataSheetComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/module-configuration/module-data-sheet-component.vue';

  defineOptions({
    name: 'RenderModuleConfigurationComponent',
  });

  const { activeSideBarOptionKey } = useSupplierGlobalComposable();
</script>
