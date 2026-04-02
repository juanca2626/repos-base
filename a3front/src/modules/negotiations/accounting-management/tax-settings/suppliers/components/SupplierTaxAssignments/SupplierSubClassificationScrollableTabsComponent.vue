<template>
  <div class="mt-3">
    <a-row>
      <a-col span="14" class="space-align-block"> {{ nameClassification }} </a-col>
      <a-col span="10">
        <a-tabs class="transport-tabs-menu" centered v-show="!isLoading" @change="handleTabChange">
          <a-tab-pane
            v-for="item in subClassification"
            :key="item.id"
            v-model:activeKey="activeTab"
            :tab="item.name"
          >
          </a-tab-pane>
        </a-tabs>
      </a-col>
    </a-row>
  </div>
</template>
<script setup lang="ts">
  import { useSupplierSubScrollableTab } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierSubScrollableTab';
  import { useSupplierTaxAssignFilterStore } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/store/supplierTaxAssignFilter.store';

  const { nameClassification, subClassification, isLoading, activeTab } =
    useSupplierSubScrollableTab();
  const supplierTaxAssignFilter = useSupplierTaxAssignFilterStore();

  const handleTabChange = (newActiveKey: string | number) => {
    activeTab.value = newActiveKey;
    supplierTaxAssignFilter.setSupplierSubClassificationId(newActiveKey as number);
  };
</script>

<style scoped lang="scss">
  .space-align-block {
    display: flex;
    align-items: center;
    padding-left: 25px;
    font-size: 16px;
    font-weight: 600;
    svg {
      margin-right: 10px;
      font-size: 16px;
    }
  }

  .transport-tabs-menu :deep(.ant-tabs-nav-wrap) {
    justify-content: right !important;
    padding-right: 45px;
  }

  .transport-tabs-menu :deep(.ant-tabs-nav) {
    &::before {
      border-bottom: none;
    }
  }

  .transport-tabs-menu :deep(.ant-tabs-tab-active) {
    border-bottom: 2px solid #cf1322;
    font-weight: 600;
    font-size: 14px;
  }

  .transport-tabs-menu :deep(.ant-tabs-tab-active::after) {
    border-bottom: none;
  }

  .transport-tabs-menu :deep(.ant-tabs-tab) {
    color: #7e8285;
  }
</style>
