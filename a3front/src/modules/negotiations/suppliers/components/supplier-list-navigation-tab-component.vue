<template>
  <a-tabs
    v-if="activeGroupTabs"
    v-model:activeKey="activeNavigationTab"
    @change="handleChangeTab"
    class="custom-tab-navigation"
  >
    <a-tab-pane v-for="tab in activeGroupTabs" :key="tab.key" :tab="tab.label" />
  </a-tabs>
</template>
<script setup lang="ts">
  import { useSupplierListNavigationTab } from '@/modules/negotiations/suppliers/composables/supplier-list-navigation-tab.composable';

  const { activeGroupTabs, activeNavigationTab, handleChangeTab } = useSupplierListNavigationTab();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-tab-navigation {
    :deep(.ant-tabs-nav-wrap) {
      overflow-x: auto;
      scrollbar-width: thin;
    }

    :deep(.ant-tabs-nav-list) {
      min-width: max-content;
      padding-left: clamp(12px, 2vw, 20px);
      padding-right: clamp(12px, 2vw, 20px);
    }

    :deep(.ant-tabs-tab) {
      color: $color-black-3;
      font-weight: 500;
      font-size: 16px;
      padding: 16px clamp(16px, 2vw, 30px);
      white-space: nowrap;
    }

    :deep(.ant-tabs-nav) {
      border-bottom: 1px solid $color-gray-ligth-4;
    }

    :deep(.ant-tabs-tab-active .ant-tabs-tab-btn) {
      color: $color-primary-strong;
      font-weight: 600;
      font-size: 16px;
    }

    :deep(.ant-tabs-ink-bar) {
      background-color: $color-primary-strong;
    }
  }
</style>
