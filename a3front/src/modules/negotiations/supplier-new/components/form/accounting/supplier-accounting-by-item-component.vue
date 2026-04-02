<template>
  <a-collapse
    class="supplier-accounting-by-item-component"
    expandIconPosition="end"
    v-model:activeKey="activeCollapseKey"
  >
    <template v-slot:expandIcon="{ isActive }">
      <font-awesome-icon
        :icon="['fa', 'angle-up']"
        :class="{ 'rotate-0': isActive, 'rotate-180': !isActive }"
      />
    </template>
    <a-collapse-panel key="accounting">
      <template #header>
        <div class="title-header">Contabilidad</div>
        <div class="progress-header">
          {{
            `${getProgressCountFormComponent('complete', 'accounting')} de ${getProgressCountFormComponent('total', 'accounting')} completados`
          }}
        </div>
      </template>

      <div :key="supplierId || 'new-supplier'">
        <template v-if="isSupplierFormActive">
          <SunatInformationComponent :key="`accounting-sunat-${supplierId || 'new'}`" />
        </template>
        <template v-else>
          <ModuleAccountingInformationSunatComponent
            :key="`accounting-module-${supplierId || 'new'}`"
          />
        </template>
      </div>
    </a-collapse-panel>
  </a-collapse>
</template>

<script setup lang="ts">
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
  import ModuleAccountingInformationSunatComponent from '@/modules/negotiations/supplier-new/components/form/accounting/form/module-configuration/module-accounting-information-sunat-component.vue';
  import SunatInformationComponent from '@/modules/negotiations/supplier-new/components/form/accounting/form/supplier-registration/sunat-information-component.vue';

  defineOptions({
    name: 'SupplierAccountingByItemComponent',
  });

  const { supplierId, isSupplierFormActive, activeCollapseKey, getProgressCountFormComponent } =
    useSupplierGlobalComposable();
</script>

<style lang="scss">
  .supplier-accounting-by-item-component {
    background: #ffffff;

    .ant-collapse-content-active {
      border-top: 1px solid transparent;
    }

    .ant-collapse-content-inactive {
      border-top: 1px solid transparent;
    }

    .ant-collapse-content-box {
      padding: 0 16px 16px 16px !important;
    }

    .title-header {
      color: #212121;
      font-weight: 600;
      font-size: 20px;
      line-height: 28px;
    }

    .progress-header {
      font-weight: 400;
      font-size: 16px;
      line-height: 24px;
      color: #7c7c7c;
    }
  }
</style>
