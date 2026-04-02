<template>
  <div class="mt-1">
    <total-records-added-component :total="pagination.total" />

    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="id"
      class="custom-table-suppliers"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <a-dropdown :trigger="['click', 'hover']" placement="bottomRight">
            <template #overlay>
              <a-menu class="custom-dropdown-actions">
                <a-menu-item key="1">
                  <div class="container-menu-item">
                    <font-awesome-icon :icon="['far', 'pen-to-square']" class="icon-menu-item" />
                    <span> Editar </span>
                  </div>
                </a-menu-item>
                <a-menu-item key="2">
                  <div class="container-menu-item">
                    <font-awesome-icon :icon="['fas', 'ban']" class="icon-menu-item" />
                    <span> Inactivar </span>
                  </div>
                </a-menu-item>
                <a-menu-item key="3">
                  <div class="container-menu-item">
                    <font-awesome-icon :icon="['far', 'clone']" class="icon-menu-item" />
                    <span> Clonar </span>
                  </div>
                </a-menu-item>
              </a-menu>
            </template>
            <font-awesome-icon class="icon-actions cursor-pointer" :icon="['fas', 'ellipsis']" />
          </a-dropdown>
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
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { useSupplierList } from '@/modules/negotiations/suppliers/composables/supplier-list.composable';
  import TotalRecordsAddedComponent from '@/modules/negotiations/suppliers/components/partials/total-records-added-component.vue';

  const { data, columns, pagination, isLoading, onChange } = useSupplierList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .custom-table-suppliers {
    :deep(table) {
      border: 1px $color-white-4 solid;
    }

    :deep(.ant-table-container) {
      border-radius: 8px;
      overflow: hidden;
    }

    :deep(.ant-table-thead) {
      .ant-table-cell {
        background: $color-gray-ice;
        border-radius: 0 !important;
        font-weight: 500;
        font-size: 16px;
        color: $color-black-2;

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

  .custom-dropdown-actions {
    width: 180px;
    border-radius: 8px !important;
    padding: 0 !important;
    :deep(.ant-dropdown-menu-item) {
      padding: 16px;

      .ant-dropdown-menu-title-content {
        span {
          font-weight: 400;
          font-size: 16px;
        }
      }
    }

    .container-menu-item {
      display: flex;
      align-items: center;
      gap: 2px;
    }

    .icon-menu-item {
      width: 20px;
      height: 20px;
    }
  }

  .icon-actions {
    height: 20px;
    width: 20px;
  }
</style>
