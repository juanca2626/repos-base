<template>
  <div class="mt-4">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="id"
      class="custom-service-table"
    >
      <template #headerCell="{ column }">
        <template v-if="column.key !== 'action'">
          <HeaderColumnFilterComponent :columnTitle="column.title" />
        </template>
      </template>

      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <div class="container-actions">
            <div class="cursor-pointer">
              <font-awesome-icon
                :icon="['far', 'pen-to-square']"
                class="btn-icon"
                @click="handleEdit(record.id)"
              />
            </div>
            <div class="cursor-pointer">
              <font-awesome-icon :icon="['far', 'clone']" class="btn-icon" />
            </div>
            <div class="cursor-pointer">
              <font-awesome-icon
                @click="handleDestroy(record.id)"
                :icon="['far', 'fa-trash-can']"
                class="btn-icon"
              />
            </div>
          </div>
        </template>
      </template>
    </a-table>
    <div class="pagination-wrapper">
      <CustomPagination
        v-model:current="pagination.current"
        v-model:pageSize="pagination.pageSize"
        :total="pagination.total"
        :disabled="data?.length === 0"
        @change="onChange"
      />
    </div>
    <contextHolder />
  </div>
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import HeaderColumnFilterComponent from '@/modules/negotiations/products/general/components/partials/HeaderColumnFilterComponent.vue';
  import { useProductList } from '@/modules/negotiations/products/general/composables/useProductList';

  const {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    contextHolder,
    handleDestroy,
    handleEdit,
  } = useProductList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 16px;

    .btn-icon {
      width: 20px;
      height: 20px;
      color: #1284ed;
    }
  }

  .pagination-wrapper {
    display: flex;
    justify-content: flex-start;
  }

  :deep(.ant-pagination-item-active) {
    border-color: $color-black-4 !important;
    background-color: $color-black-4 !important;
    color: $color-white;
  }

  .custom-service-table {
    :deep(table) {
      border: 1px $color-white-4 solid;
    }

    :deep(.ant-table-container) {
      border-radius: 8px;
      overflow: hidden;
    }

    :deep(.ant-table-thead) {
      .ant-table-cell {
        background: $color-black;
        border-radius: 0 !important;
        font-weight: 500;
        font-size: 16px;
        color: $color-white;
        padding: 16px;

        &::before {
          display: none !important;
        }
      }
    }

    :deep(.ant-table-tbody) {
      .ant-table-cell {
        font-size: 16px;
        color: $color-black-graphite;
      }
    }
  }
</style>
