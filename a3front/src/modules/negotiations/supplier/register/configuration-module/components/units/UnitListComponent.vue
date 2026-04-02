<template>
  <div class="table-negotiation-services">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="type_unit_transport_id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'status'">
          <a-switch v-model:checked="record.status" />
        </template>
        <template v-else-if="column.key === 'action'">
          <span class="show-technical-sheet" @click="handleShowTechnicalSheet">
            Ver ficha técnica
          </span>
        </template>
      </template>
    </a-table>
    <CustomPagination
      v-model:current="pagination.current"
      v-model:pageSize="pagination.pageSize"
      :total="pagination.total"
      :disabled="data?.length === 0"
      @change="onChange"
    />
  </div>
</template>

<script setup lang="ts">
  import { type PropType } from 'vue';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { useUnitList } from '@/modules/negotiations/supplier/register/configuration-module/composables/units/useUnitList';
  import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const props = defineProps({
    selectedLocation: {
      type: Object as PropType<OperationLocationData>,
      required: true,
    },
  });

  const { data, columns, pagination, isLoading, onChange, handleShowTechnicalSheet } = useUnitList(
    props.selectedLocation
  );
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .show-technical-sheet {
    font-size: 14px;
    font-weight: 400;
    color: $color-blue;
    text-decoration: underline;
    cursor: pointer;
  }
</style>
