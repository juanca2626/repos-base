<template>
  <div class="module-operations" style="padding-bottom: 10px">
    <a-title-section title="Servicios programados" icon="calendar" />

    <template v-if="dataStore.kpi && providerStore.getContract">
      <a-row
        justify="space-between"
        class="px-5"
        style="display: flex; flex-wrap: nowrap; padding-top: 25px"
      >
        <a-col :span="24" style="display: flex; flex-wrap: nowrap; gap: 10px">
          <CustomIndicatorComponent
            v-if="providerStore.getContract === 'F'"
            :total="dataStore.kpi.confirmed.total"
            :icon="['far', 'circle-check']"
            iconPosition="right"
            :titleColor="indicatorColors.green"
            :bgButton="indicatorColors.default"
            :textButton="indicatorColors.default"
            :colorArrow="indicatorColors.red"
            :buttons="[{ label: 'Confirmados', action: 'confirmed' }]"
            @click="handleIndicatorClick"
          />

          <CustomIndicatorComponent
            v-if="providerStore.getContract === 'F'"
            :total="dataStore.kpi.unconfirmed.total"
            :icon="['far', 'clock']"
            iconPosition="right"
            :titleColor="indicatorColors.orange"
            :bgButton="indicatorColors.default"
            :textButton="indicatorColors.default"
            :colorArrow="indicatorColors.red"
            :buttons="[{ label: 'Sin confirmar', action: 'unconfirmed' }]"
            @click="handleIndicatorClick"
          />

          <CustomIndicatorComponent
            :total="dataStore.kpi.no_report.total"
            :icon="['fas', 'circle-exclamation']"
            iconPosition="right"
            :titleColor="indicatorColors.yellow"
            :bgButton="indicatorColors.default"
            :textButton="indicatorColors.default"
            :colorArrow="indicatorColors.red"
            :buttons="[{ label: 'Sin reportes', action: 'no_report' }]"
            @click="handleIndicatorClick"
          />
        </a-col>
      </a-row>

      <a-row class="px-5">
        <a-col :span="24"> <a-divider /></a-col>
      </a-row>
    </template>

    <a-row class="px-5">
      <a-typography-text strong style="font-size: 16px">
        Complete los siguientes campos para filtrar el listado de servicios programados:
      </a-typography-text>
    </a-row>

    <a-row>
      <a-col :span="24">
        <FormComponent />
      </a-col>
    </a-row>

    <a-row class="px-5">
      <a-col :span="24">
        <TableComponent
          :loading="dataStore.loading"
          :data="dataStore.services"
          :pagination="tableStore.pagination"
        />
      </a-col>
    </a-row>
  </div>

  <!-- <AdditionalComponent :showDrawer="showDrawer" @handlerShowDrawer="" /> -->
</template>

<script setup lang="ts">
  /*** COMPONENTS ***/
  import ATitleSection from '@/components/backend/ATitleSection.vue';

  import FormComponent from '@operations/modules/providers/components/FormComponent.vue';
  import TableComponent from '@operations/modules/providers/components/TableComponent.vue';

  import { useTableStore } from '@operations/modules/providers/store/table.store';
  import { useDataStore } from '@operations/modules/providers/store/data.store';
  import { indicatorColors } from '@/modules/operations/shared/utils/colorsUtils';
  import { useIndicatorActions } from '../composables/useIndicatorActions';
  import { useProviderStore } from '../store/providerStore';
  import CustomIndicatorComponent from '@/modules/operations/shared/components/CustomIndicatorComponent.vue';

  const { handleIndicatorClick } = useIndicatorActions();

  // const formStore = useFormStore();
  const tableStore = useTableStore();
  const dataStore = useDataStore();
  const providerStore = useProviderStore();

  // Método para resetear filtros
  // const resetFilters = () => {
  //   formStore.resetFilters();
  // };
</script>

<style lang="scss" scoped>
  ::v-deep(.custom-select svg) {
    fill: #7e8285;
    position: absolute;
    left: -455px;
    width: 15px;
  }
  ::v-deep(.custom-select .ant-select-selection-placeholder),
  ::v-deep(.custom-select .ant-select-selector input[type='search']) {
    padding-left: 20px;
  }
</style>
