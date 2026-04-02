<template>
  <div class="module-operations" style="padding-bottom: 10px">
    <a-title-section title="Listado de servicios" icon="calendar" />

    <a-row class="py-2 px-5">
      <a-typography-text strong style="font-size: 16px">
        Complete los siguientes campos para filtrar el listado de servicios:
      </a-typography-text>
    </a-row>

    <!-- <TableOpeFilterComponent @filterChanged="updateFilters" /> -->
    <FormComponent />

    <a-row justify="space-between" class="px-5" style="display: flex; flex-wrap: nowrap">
      <a-col :span="5">
        <CustomIndicatorComponent
          v-if="dataStore.kpi"
          :total="dataStore.kpi.upcoming.total"
          :icon="['far', 'circle-check']"
          :color="indicatorColors.default"
          subtitle="Inician en 2-5 días"
          :buttons="[{ label: 'Ver servicios', action: 'upcoming' }]"
          @click="handleIndicatorClick"
        />
      </a-col>

      <a-col :span="1" justify="center" align="center">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="3"
          height="105"
          viewBox="0 0 3 105"
          fill="none"
        >
          <path
            d="M1.20001 1L1.20001 104"
            stroke="#E4E5E6"
            stroke-width="2"
            stroke-linecap="round"
          />
        </svg>
      </a-col>
      <a-col :span="18" justify="space-between" class="" style="display: flex; flex-wrap: nowrap">
        <div style="flex: 1; margin-right: 10px">
          <CustomIndicatorComponent
            v-if="dataStore.kpi"
            :total="dataStore.kpi.unassigned.total"
            :icon="['fas', 'list-check']"
            :titleColor="indicatorColors.blue"
            :bgButton="indicatorColors.blue"
            subtitle="Sin asignar"
            :buttons="[
              { label: `${dataStore.kpi.unassigned.trp} TRP`, action: 'unassigned', type: 'trp' },
              { label: `${dataStore.kpi.unassigned.gui} GUI`, action: 'unassigned', type: 'gui' },
            ]"
            @click="handleIndicatorClick"
          />
        </div>

        <div style="flex: 1; margin-right: 10px">
          <CustomIndicatorComponent
            v-if="dataStore.kpi"
            :total="dataStore.kpi.unconfirmed.total"
            :icon="['far', 'clock']"
            :titleColor="indicatorColors.orange"
            :bgButton="indicatorColors.orange"
            isColor
            subtitle="Sin confirmar"
            :buttons="[
              { label: `${dataStore.kpi.unconfirmed.trp} TRP`, action: 'unconfirmed', type: 'trp' },
              { label: `${dataStore.kpi.unconfirmed.gui} GUI`, action: 'unconfirmed', type: 'gui' },
            ]"
            @click="handleIndicatorClick"
          />
        </div>

        <div style="flex: 1; margin-right: 10px">
          <CustomIndicatorComponent
            v-if="dataStore.kpi && dataStore.kpi.canceled"
            :total="dataStore.kpi.canceled.total"
            :icon="['fas', 'ban']"
            :titleColor="indicatorColors.red"
            :bgButton="indicatorColors.red"
            isColor
            subtitle="Rechazados"
            :buttons="[
              { label: `${dataStore.kpi.canceled.trp} TRP`, action: 'canceled', type: 'trp' },
              { label: `${dataStore.kpi.canceled.gui} GUI`, action: 'canceled', type: 'gui' },
            ]"
            @click="handleIndicatorClick"
          />
        </div>

        <div style="flex: 1">
          <CustomIndicatorComponent
            v-if="dataStore.kpi && dataStore.kpi.without_service_order"
            :total="dataStore.kpi.without_service_order.total"
            :icon="['far', 'envelope']"
            :titleColor="indicatorColors.purple"
            :bgButton="indicatorColors.purple"
            isColor
            subtitle="Sin orden de servicio"
            :buttons="[
              {
                label: `${dataStore.kpi.without_service_order.trp} TRP`,
                action: 'without_service_order',
                type: 'trp',
              },
              {
                label: `${dataStore.kpi.without_service_order.gui} GUI`,
                action: 'without_service_order',
                type: 'gui',
              },
            ]"
            @click="handleIndicatorClick"
          />
        </div>
      </a-col>
    </a-row>

    <a-row class="py-2 px-5">
      <a-col :span="24">
        <a-flex justify="flex-end" align="center">
          <a-button @click="resetFilters" type="link" justify="center" align="center">
            <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" style="padding-top: 5px" />
            Limpiar filtros
          </a-button>
        </a-flex>
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
</template>

<script setup lang="ts">
  /*** COMPONENTS ***/
  import ATitleSection from '@/components/backend/ATitleSection.vue';

  import FormComponent from '@operations/modules/service-management/components/FormComponent.vue';
  import TableComponent from '@operations/modules/service-management/components/TableComponent.vue';

  /*** STORES ***/
  import { useFormStore } from '@operations/modules/service-management/store/form.store';
  import { useTableStore } from '@operations/modules/service-management/store/table.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';
  import { indicatorColors } from '@/modules/operations/shared/utils/colorsUtils';
  import { useIndicatorActions } from '../composables/useIndicatorActions';
  import CustomIndicatorComponent from '@/modules/operations/shared/components/CustomIndicatorComponent.vue';

  const { handleIndicatorClick } = useIndicatorActions();

  const formStore = useFormStore();
  const tableStore = useTableStore();
  const dataStore = useDataStore();

  // Método para resetear filtros
  const resetFilters = () => {
    formStore.resetFilters();
  };
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
