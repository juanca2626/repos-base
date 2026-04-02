<template>
  <a-collapse
    class="supplier-treasury-by-item-component"
    expandIconPosition="end"
    v-model:activeKey="activeCollapseKey"
  >
    <template v-slot:expandIcon="{ isActive }">
      <font-awesome-icon
        :icon="['fa', 'angle-up']"
        :class="{ 'rotate-0': isActive, 'rotate-180': !isActive }"
      />
    </template>
    <a-collapse-panel key="treasury">
      <template #header>
        <div class="title-header">Tesorería</div>
        <div class="progress-header">
          {{
            `${getProgressCountFormComponent('complete', 'treasury')} de ${getProgressCountFormComponent('total', 'treasury')} completados`
          }}
        </div>
      </template>
      <div :key="supplierId || 'new-supplier'">
        <template v-if="isSupplierFormActive">
          <BasicInformationComponent :key="`treasury-basic-${supplierId || 'new'}`" />
        </template>
        <template v-else>
          <RenderModuleConfigurationTreasuryComponent
            :key="`treasury-module-${supplierId || 'new'}`"
          />
        </template>
      </div>
    </a-collapse-panel>
  </a-collapse>
</template>

<script setup lang="ts">
  import BasicInformationComponent from '@/modules/negotiations/supplier-new/components/form/treasury/form/supplier-registration/basic-information-component.vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
  import RenderModuleConfigurationTreasuryComponent from '@/modules/negotiations/supplier-new/components/form/treasury/form/module-configuration/render-module-configuration-treasury-component.vue';

  defineOptions({
    name: 'SupplierTreasuryByItemComponent',
  });

  const { supplierId, isSupplierFormActive, activeCollapseKey, getProgressCountFormComponent } =
    useSupplierGlobalComposable();
</script>

<style lang="scss">
  .supplier-treasury-by-item-component {
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
