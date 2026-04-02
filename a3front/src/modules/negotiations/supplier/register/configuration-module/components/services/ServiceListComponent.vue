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
          <span class="show-component" @click="handleShowComponent"> Ver componente </span>
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
  import { useServiceList } from '@/modules/negotiations/supplier/register/configuration-module/composables/services/useServiceList';
  import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const props = defineProps({
    selectedLocation: {
      type: Object as PropType<OperationLocationData>,
      required: true,
    },
  });

  const { data, columns, pagination, isLoading, onChange, handleShowComponent } = useServiceList(
    props.selectedLocation
  );
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .show-component {
    font-size: 14px;
    font-weight: 500;
    color: $color-blue;
    text-decoration: underline;
    cursor: pointer;
  }
</style>
