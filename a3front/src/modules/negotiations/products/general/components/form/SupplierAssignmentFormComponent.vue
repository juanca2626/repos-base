<template>
  <div class="supplier-assignment-form">
    <span class="form-title"> Asignar proveedores </span>

    <SupplierAssignmentFilterComponent />

    <div class="supplier-assignment-container">
      <div class="list-container">
        <span class="list-title"> Listado de proveedores </span>

        <template v-if="suppliersToAssign.length === 0">
          <div class="mt-4">
            <span class="empty-assignment"> No hay proveedores para asignar </span>
          </div>
        </template>
        <template v-else>
          <div class="mt-3">
            <a-table
              :columns="columns"
              :data-source="suppliersToAssign"
              :row-selection="{
                selectedRowKeys: selectedSupplierKeys,
                onChange: handleSupplierSelectChange,
              }"
              :pagination="false"
              row-key="supplierOriginalId"
              class="custom-table"
              :scroll="{ y: toAssignTableHeight }"
            >
              <template #headerCell="{ column }">
                <div v-if="column.key === 'all'" class="header-cell-container">
                  <span class="header-cell">
                    {{ column.title }}
                  </span>
                  <template v-if="isAssignMultiple">
                    <a-button
                      size="large"
                      type="primary"
                      class="assignment-button"
                      @click="handleAssignMultipleSuppliers()"
                      :loading="isLoadingMultipleSuppliers"
                    >
                      <div class="text-container">
                        <template v-if="!isLoadingMultipleSuppliers">
                          Asignar
                          <font-awesome-icon icon="fa-solid fa-arrow-right" />
                        </template>
                      </div>
                    </a-button>
                  </template>
                </div>
              </template>

              <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'all'">
                  <div class="assignment-item">
                    <span class="assignment-item-text">
                      {{ record.code }} - {{ record.name }}
                    </span>

                    <template v-if="!isAssignMultiple">
                      <a-button
                        size="large"
                        type="primary"
                        class="assignment-button"
                        :loading="record.isLoading"
                        @click="handleAssignSupplier(record)"
                      >
                        <div class="text-container">
                          <template v-if="!record.isLoading">
                            Asignar
                            <font-awesome-icon icon="fa-solid fa-arrow-right" />
                          </template>
                        </div>
                      </a-button>
                    </template>
                  </div>
                </template>
              </template>
            </a-table>
          </div>
        </template>
      </div>
      <div class="list-container">
        <span class="list-title"> Proveedores asignados </span>
        <template v-if="assignedSuppliers.length === 0">
          <div class="mt-4">
            <span class="empty-assignment"> Aún no has seleccionado proveedores </span>
          </div>
        </template>
        <template v-else>
          <div class="assigned-supplier-container">
            <div class="list-assigned-suppliers">
              <div
                v-for="(row, index) in assignedSuppliers"
                class="item"
                :class="[index > 0 ? 'common-height' : 'first-height']"
              >
                <span class="item-text"> {{ row.code }} - {{ row.name }} </span>
                <div class="cursor-pointer" @click="handleDeleteAssignedSuppliers(row)">
                  <font-awesome-icon :icon="['fas', 'fa-xmark']" class="remove-icon" />
                </div>
              </div>
            </div>
          </div>
        </template>
      </div>
    </div>

    <div class="mt-4 mb-1">
      <a-button
        size="large"
        type="primary"
        class="btn-save"
        :disabled="assignedSuppliers.length === 0"
        @click="handleSave"
      >
        Guardar datos
      </a-button>
    </div>
  </div>
</template>
<script setup lang="ts">
  import { useSupplierAssignmentForm } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentForm';
  import SupplierAssignmentFilterComponent from '@/modules/negotiations/products/general/components/form/SupplierAssignmentFilterComponent.vue';

  const {
    columns,
    suppliersToAssign,
    assignedSuppliers,
    selectedSupplierKeys,
    toAssignTableHeight,
    isAssignMultiple,
    isLoadingMultipleSuppliers,
    handleSupplierSelectChange,
    handleAssignSupplier,
    handleDeleteAssignedSuppliers,
    handleAssignMultipleSuppliers,
    handleSave,
  } = useSupplierAssignmentForm();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .assigned-supplier-container {
    margin-top: 24px;
  }

  .list-assigned-suppliers {
    max-height: 608px;
    overflow-y: auto;
    padding-right: 16px;

    // scroll personalizado
    &::-webkit-scrollbar-track {
      background: $color-white-3;
      border-radius: 8px !important;
    }

    &::-webkit-scrollbar {
      width: 8px;
    }

    &::-webkit-scrollbar-thumb {
      background: $color-black-4;
      border-radius: 8px !important;
    }

    &::-webkit-scrollbar-thumb:hover {
      background-color: darken($color-black-4, 3%);
    }

    .first-height {
      height: 48px;
    }

    .common-height {
      height: 64px;
    }

    .item {
      display: flex;
      align-items: center;
      justify-content: space-between;

      padding: 12px 0;
      border-bottom: 1px solid $color-gray-ligth-4;

      &-text {
        font-size: 14px;
        font-weight: 500;
      }
    }

    .remove-icon {
      width: 22px;
      height: 22px;
      color: $color-black-4;
    }
  }

  .btn-save {
    height: 50px;
    min-width: 201px;
    font-size: 16px !important;
    font-weight: 600 !important;
  }

  .supplier-assignment-form {
    margin-top: 1px;
    // min-height: 780px;
  }

  .empty-assignment {
    font-size: 16px;
    font-weight: 400;
  }

  .supplier-assignment-container {
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
    gap: 24px;
    margin-top: 24px;
    align-items: start;

    .list-title {
      font-size: 20px;
      font-weight: 600;
    }

    .list-container {
      border-radius: 8px;
      border: 1px solid $color-gray-ligth-4;
      padding: 24px 8px 24px 24px;

      &-assigned-supplier {
        min-height: 120px;
      }
    }

    .custom-table {
      // custom scrollbar
      :deep(.ant-table-body) {
        &::-webkit-scrollbar-track {
          background: $color-white-3;
          border-radius: 8px !important;
        }

        &::-webkit-scrollbar {
          width: 8px;
        }

        &::-webkit-scrollbar-thumb {
          background: $color-black-4;
          border-radius: 8px !important;
        }

        &::-webkit-scrollbar-thumb:hover {
          background-color: darken($color-black-4, 3%);
        }
      }
      // custom scrollbar

      :deep(.ant-table-row-selected),
      :deep(.ant-table-row-selected:hover) {
        td {
          background-color: $color-white;
        }
      }

      :deep(.ant-table-selection-col) {
        width: 16px !important;
      }

      :deep(.ant-checkbox-inner) {
        width: 16px !important;
        height: 16px !important;
        border-radius: 2px !important;
      }

      :deep(
        .ant-table-thead
          .ant-checkbox-checked:not(.ant-checkbox-indeterminate)
          .ant-checkbox-inner::after
      ),
      :deep(.ant-table-tbody .ant-checkbox-inner::after) {
        width: 4px !important;
        height: 8px !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -65%) rotate(45deg) !important;
        border-width: 2px !important;
      }

      :deep(.ant-table-cell) {
        padding: 12px 0 !important;
        height: 56px;
      }

      :deep(.ant-table-selection) {
        padding: 0 !important;
      }

      :deep(.ant-table-selection-column) {
        padding: 0 !important;
      }

      :deep(.ant-table-thead) {
        padding: 0 !important;
        margin: 0 !important;

        .ant-table-cell {
          background: $color-white;
          border-radius: 0 !important;
          color: $color-black;

          &::before {
            display: none !important;
          }
        }
      }

      .header-cell-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-right: 8px;

        .header-cell {
          font-size: 14px;
          font-weight: 500;
          padding-left: 8px;
        }
      }

      .assignment-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding-left: 8px;
        padding-right: 8px;

        &-text {
          font-size: 14px;
          font-weight: 500;
        }
      }

      .assignment-button {
        width: 116px;
        height: 32px;
        font-size: 16px !important;
        font-weight: 600 !important;
        border-radius: 5px;
        background: $color-white;
        color: $color-black;
        border: 1px solid $color-black;
        box-shadow: none !important;

        &:hover {
          color: $color-black;
          background: $color-white-3;
          border: 1px solid $color-black !important;
        }

        .text-container {
          display: flex;
          align-items: center;
          gap: 8px;
        }
      }
    }
  }

  .form-title {
    font-size: 20px;
    font-weight: 600;
    color: $color-black;
  }
</style>
