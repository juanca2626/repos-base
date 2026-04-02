<template>
  <div
    :class="[
      'module-negotiations custom-list-table-borderless mt-1',
      { 'mb-3': transferData.settingDetails.length > 0 },
    ]"
    v-if="existsLocations"
  >
    <a-table
      :columns="columns"
      :data-source="transferData.settingDetails"
      :pagination="false"
      :loading="isLoadingMain"
      row-key="id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="isQuantityColumn(column.key)">
          <a-tag :class="getTagClass(record[column.key])">
            {{ getTagText(record[column.key]) }}
          </a-tag>
        </template>
        <template v-else-if="column.key === 'typeUnit'">
          {{ record.typeUnitTransport.code }}
        </template>
      </template>
    </a-table>
  </div>
  <contextHolder />
</template>

<script setup lang="ts">
  import { useTypeUnitSettingList } from '@/modules/negotiations/type-unit-configurator/settings/composables/useTypeUnitSettingList';

  const {
    transferData,
    columns,
    isLoadingMain,
    existsLocations,
    contextHolder,
    isQuantityColumn,
    getTagClass,
    getTagText,
  } = useTypeUnitSettingList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
</style>
