<template>
  <div>
    <a-spin :spinning="isLoading">
      <template v-if="locationData.length > 0">
        <TabOperationLocationsComponent :data="locationData" @handleTabClick="handleTabClick" />
      </template>
      <div class="custom-tab-content">
        <ServiceListComponent :selectedLocation="selectedLocation" />
      </div>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import TabOperationLocationsComponent from '@/modules/negotiations/supplier/register/configuration-module/components/TabOperationLocationsComponent.vue';
  import ServiceListComponent from '@/modules/negotiations/supplier/register/configuration-module/components/services/ServiceListComponent.vue';
  import { useService } from '@/modules/negotiations/supplier/register/configuration-module/composables/services/useService';

  const { isLoading, locationData, selectedLocation, handleTabClick } = useService();
</script>
<style scoped lang="scss"></style>
