<template>
  <div class="module-negotiations ms-5 me-5 pb-3">
    <a-typography-text type="secondary" class="mb-3"> Listado de proveedores</a-typography-text>
    <a-table
      :columns="columns"
      :data-source="dataList"
      :pagination="false"
      :loading="isLoading"
      @change="handleTableChange"
      :row-selection="rowSelection"
      row-key="sub_classification_supplier_id"
    >
      <template #headerCell="{ column }">
        <span v-if="['code', 'name', 'ruc'].includes(column.key)" style="color: #ffffff">
          {{ column.title }}
        </span>
      </template>
    </a-table>
    <CustomPagination
      v-model:current="pagination.current"
      v-model:pageSize="pagination.pageSize"
      :total="pagination.total"
      :disabled="dataList?.length === 0"
      @change="onChange"
    />
    <contextHolder />
  </div>
</template>
<script setup lang="ts">
  import { useSupplierTaxAssignmentsList } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierTaxAssignmentsList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';

  const { columns, dataList, pagination, isLoading, rowSelection, handleTableChange, onChange } =
    useSupplierTaxAssignmentsList();
</script>
<style scoped lang="scss"></style>
