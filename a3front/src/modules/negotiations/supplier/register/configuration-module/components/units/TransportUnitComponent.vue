<template>
  <div>
    <a-spin :spinning="isLoading">
      <template v-if="locationData.length > 0">
        <TabOperationLocationsComponent :data="locationData" @handleTabClick="handleTabClick" />
      </template>
      <div class="custom-tab-content">
        <UnitListComponent :selectedLocation="selectedLocation" />
      </div>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import TabOperationLocationsComponent from '@/modules/negotiations/supplier/register/configuration-module/components/TabOperationLocationsComponent.vue';
  import UnitListComponent from '@/modules/negotiations/supplier/register/configuration-module/components/units/UnitListComponent.vue';
  import { useTransportUnit } from '@/modules/negotiations/supplier/register/configuration-module/composables/units/useTransportUnit';

  const { isLoading, locationData, selectedLocation, handleTabClick } = useTransportUnit();
</script>

<style scoped lang="scss"></style>
