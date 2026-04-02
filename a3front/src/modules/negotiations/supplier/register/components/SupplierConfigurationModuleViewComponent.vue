<template>
  <div class="configuration-module-view">
    <div class="header-section">
      <ProgressBar />

      <TabsSection />
    </div>

    <DetailsSection />

    <div class="config-services">
      <component :is="renderTabOptionCollaborator" />
    </div>
  </div>
</template>

<script setup lang="ts">
  import TabsSection from '@/modules/negotiations/supplier/register/components/ConfigurationModule/TabsSection.vue';
  import DetailsSection from '@/modules/negotiations/supplier/register/components/ConfigurationModule/DetailsSection.vue';
  import ProgressBar from '@/modules/negotiations/supplier/register/components/ConfigurationModule/ProgressBar.vue';
  import { useSupplierTabView } from '@/modules/negotiations/supplier/register/composables/useSupplierTabView';

  const { renderTabOptionCollaborator } = useSupplierTabView();
</script>

<style>
  .configuration-module-view {
    .header-section {
      display: flex;
      justify-content: space-between;
      margin-bottom: 1rem;
    }

    .config-services {
      .ant-tabs-nav-operations {
        display: none;
      }
    }
  }
</style>
