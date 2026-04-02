<template>
  <div class="flex justify-between items-center mb-3">
    <div class="flex items-center">
      <a-select
        v-model:value="selectedDepartureId"
        placeholder="Filtrar por salida"
        :options="departureOptions"
        allow-clear
        show-search
        :filter-option="filterOption"
        style="width: 200px; margin-right: 1rem"
      />
      <a-select
        v-model:value="selectedClientId"
        placeholder="Filtrar por cliente"
        :options="clientOptions"
        allow-clear
        show-search
        :filter-option="filterOption"
        style="width: 200px; margin-right: 1rem"
      />
    </div>
    <div class="flex items-center">
      <a-button @click="toggleAllRows">
        {{ expandedRowKeys.length === filteredGroups.length ? 'Colapsar Todos' : 'Expandir Todos' }}
      </a-button>
    </div>
  </div>

  <a-table
    :columns="departureColumns"
    :data-source="filteredGroups"
    :pagination="false"
    :loading="isLoading"
    :row-key="departureRowKey"
    v-model:expandedRowKeys="expandedRowKeys"
  >
    <template #bodyCell="{ column, record: itemRecord }">
      <template v-if="column.key === 'departure'">
        <div class="font-semibold">Salida {{ itemRecord.departure.name }}</div>
      </template>
      <template v-if="column.key === 'total_pax'">
        <div class="font-semibold">{{ itemRecord.total_pax }}</div>
      </template>
    </template>
    <!-- Tabla hija -->
    <template #expandedRowRender="{ record }">
      <a-table
        :columns="itemColumns"
        :data-source="mapItems(record.items)"
        :pagination="false"
        row-key="id"
        size="small"
      >
        <template #bodyCell="{ column, record: itemRecord }">
          <template v-if="column.key === 'file'">
            <div>{{ itemRecord.file }}</div>
          </template>
          <template v-if="column.key === 'name_pax'">
            <div>{{ itemRecord.passenger_group_name }}</div>
          </template>
          <template v-if="column.key === 'program'">
            <div class="font-semibold">{{ itemRecord.departure_program.program.name }}</div>
            <small>Fecha de salida: {{ formatDate(itemRecord.departure_program.date) }}</small>
          </template>
          <template v-if="column.key === 'user'">
            <div>{{ itemRecord.user.name }}</div>
          </template>
          <template v-if="column.key === 'options'">
            <a-button type="link" @click="editSeriesProgram(itemRecord)"> Editar </a-button>
            <a-button type="link" @click="showPromiseConfirm(String(itemRecord.id))">
              Eliminar
            </a-button>
          </template>
        </template>
      </a-table>
    </template>
  </a-table>
  <contextHolder />
</template>

<script lang="ts" setup>
  import { defineProps } from 'vue';
  import moment from 'moment';
  import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';
  import { useSeriesProgramsList } from '@/modules/series-tracking/series-programs/composables/useSeriesProgramsList';

  const props = defineProps<{ filters: FilterDatesInterface }>();

  const formatDate = (value: any) => {
    if (!value) return '';
    return moment(value).format('DD/MM/YYYY');
  };

  const filterOption = (inputValue: string, option: any) => {
    return option.label.toLowerCase().includes(inputValue.toLowerCase());
  };

  const {
    departureColumns,
    itemColumns,
    isLoading,
    contextHolder,
    showPromiseConfirm,
    departureRowKey,
    mapItems,
    editSeriesProgram,
    expandedRowKeys,
    toggleAllRows,
    selectedDepartureId,
    filteredGroups,
    departureOptions,
    selectedClientId,
    clientOptions,
  } = useSeriesProgramsList(props);
</script>

<style scoped lang="scss">
  .font-semibold {
    font-weight: 600;
  }
  .text-xs {
    font-size: 12px;
  }
  .text-red-500 {
    color: #ef4444;
  }
  .flex {
    display: flex;
  }
  .justify-between {
    justify-content: space-between;
  }
  .items-center {
    align-items: center;
  }
  .mb-3 {
    margin-bottom: 1rem;
  }
</style>
