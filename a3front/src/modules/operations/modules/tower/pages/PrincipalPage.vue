<template>
  <div class="module-operations" style="padding-bottom: 10px">
    <a-title-section title="Torre de control" icon="calendar" />

    <a-row class="py-2 px-5">
      <a-typography-text strong style="font-size: 16px">
        Complete los siguientes campos para filtrar el listado de servicios:
      </a-typography-text>
    </a-row>

    <FormComponent />

    <a-row justify="space-between" class="py-0 px-5" style="display: flex; flex-wrap: nowrap">
      <div style="flex: 1; margin-right: 10px">
        <CustomIndicatorComponent
          v-if="dataStore?.kpi"
          :total="dataStore.kpi.unassigned.total"
          :icon="['fas', 'list-check']"
          :titleColor="indicatorColors.blue"
          :bgButton="indicatorColors.blue"
          subtitle="Sin asignar"
          :buttons="[]"
          @click="handleIndicatorClick"
        />
      </div>
      <div style="flex: 1; margin-right: 10px">
        <CustomIndicatorComponent
          v-if="dataStore.kpi"
          :total="dataStore.kpi.unconfirmed?.total"
          :icon="['far', 'clock']"
          :titleColor="indicatorColors.orange"
          :bgButton="indicatorColors.orange"
          isColor
          subtitle="Sin confirmar"
          :buttons="[]"
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
          :buttons="[]"
          @click="handleIndicatorClick"
        />
      </div>
      <div style="flex: 1; margin-right: 10px">
        <CustomIndicatorComponent
          v-if="dataStore.kpi && dataStore.kpi.without_service_order"
          :total="dataStore.kpi.without_service_order.total"
          :icon="['far', 'envelope']"
          :titleColor="indicatorColors.purple"
          :bgButton="indicatorColors.purple"
          isColor
          subtitle="Sin orden de servicio"
          :buttons="[]"
          @click="handleIndicatorClick"
        />
      </div>
      <div
        style="
          display: flex;
          flex: 1;
          justify-content: flex-end;
          align-items: flex-end;
          position: relative;
        "
      >
        <a-button
          @click="resetFilters"
          type="link"
          style="position: absolute; bottom: 8px; right: 8px"
        >
          <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" style="padding-top: 5px" />
          Limpiar filtros
        </a-button>
      </div>
    </a-row>

    <a-row class="pt-5 px-5">
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
  import ATitleSection from '@/components/backend/ATitleSection.vue';
  import FormComponent from '@operations/modules/tower/components/FormComponent.vue';
  import TableComponent from '@operations/modules/tower/components/TableComponent.vue';

  /** STORES **/
  import { useFormStore } from '@operations/modules/tower/store/form.store';
  import { useTableStore } from '@operations/modules/tower/store/table.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';

  import { indicatorColors } from '@/modules/operations/shared/utils/colorsUtils';
  import CustomIndicatorComponent from '@/modules/operations/shared/components/CustomIndicatorComponent.vue';

  const handleIndicatorClick = () => {
    console.log('go!');
  };

  const formStore = useFormStore();
  const tableStore = useTableStore();
  const dataStore = useDataStore();

  console.log('dataStore', dataStore);

  const resetFilters = () => {
    formStore.resetFilters();
  };

  // onMounted(() => {
  //   formStore.fetchServicesWithParams(); // Llama a la API al montar el componente
  // });

  // const updateFilters = (filters: any) => {
  //   formStore.clearDynamicParams();
  //   Object.assign(formStore.formSearch, filters);
  // };

  // watch(
  //   [() => formStore.processedParams, () => tableStore.pagination],
  //   debounce(() => {
  //     formStore.fetchServicesWithParams();
  //   }, 300), // 300ms de retraso
  //   { deep: true }
  // );
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
