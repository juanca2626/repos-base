<template>
  <div class="supplier-assignment-list">
    <div>
      <span class="list-title"> Lista de proveedores asignados </span>
    </div>
    <a-table
      :columns="columns"
      :data-source="assignedSuppliers"
      :pagination="false"
      row-key="id"
      class="custom-assignment-table"
    >
      <template #headerCell="{ column }">
        <template v-if="column.key !== 'action'">
          <HeaderColumnFilterComponent
            :columnTitle="column.title"
            :alignCenter="column.align === 'center'"
          />
        </template>
      </template>

      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'classificationName'">
          <span> {{ record.supplierClassification.name }} </span>
        </template>
        <template v-else-if="column.key === 'code'">
          <span> {{ record.code }} - {{ record.name }} </span>
        </template>
        <template v-else-if="column.key === 'progress'">
          <template v-if="record.productSupplier">
            <div class="cell-center">
              <span
                class="progress-text"
                :class="[
                  record.productSupplier.progress > 80
                    ? 'progress-text-complete'
                    : 'progress-text-incomplete',
                ]"
              >
                {{ record.productSupplier.progress }}%
              </span>
            </div>
          </template>
        </template>
        <template v-else-if="column.key === 'status'">
          <div>
            <span v-if="record.productSupplier?.status">
              <a-tag color="#DFFFE9" class="tag-supplier-active"> Activo </a-tag>
            </span>
            <span v-else>
              <a-tag color="#feefef" class="tag-supplier-inactive"> Inactivo </a-tag>
            </span>
          </div>
        </template>
        <template v-else-if="column.key === 'action'">
          <div class="container-actions">
            <div
              class="icon-wrapper cursor-pointer"
              @click="handleConfiguration(record.supplierOriginalId, record.productSupplierId)"
            >
              <template v-if="loadingConfigurationId === record.productSupplierId">
                <font-awesome-icon :icon="['fas', 'spinner']" class="btn-icon loading-icon" spin />
              </template>
              <template v-else>
                <font-awesome-icon :icon="['far', 'pen-to-square']" class="btn-icon" />
              </template>
            </div>
            <div class="icon-wrapper cursor-pointer">
              <icon-ban :height="25" :width="25" color="#1284ED" />
            </div>
          </div>
        </template>
      </template>
    </a-table>
    <div class="mt-4">
      <a-button type="primary" class="assignment-supplier-button" @click="handleAssignmentSupplier">
        <font-awesome-icon :icon="['fas', 'plus']" />
        Asignar proveedor
      </a-button>
    </div>

    <div v-if="configurationFormComponent">
      <component
        :is="configurationFormComponent"
        v-model:showDrawerForm="showDrawertConfigurationForm"
        :supplierOriginalId="supplierOriginalId"
        :productSupplierId="productSupplierId"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import IconBan from '@/modules/negotiations/suppliers/icons/icon-ban.vue';
  import HeaderColumnFilterComponent from '@/modules/negotiations/products/general/components/partials/HeaderColumnFilterComponent.vue';
  import { useSupplierAssignmentList } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentList';

  const {
    columns,
    assignedSuppliers,
    showDrawertConfigurationForm,
    configurationFormComponent,
    supplierOriginalId,
    productSupplierId,
    loadingConfigurationId,
    handleAssignmentSupplier,
    handleConfiguration,
  } = useSupplierAssignmentList();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .supplier-assignment-list {
    margin-top: 24px;

    .list-title {
      font-size: 20px;
      font-weight: 600;
    }

    .custom-assignment-table {
      margin-top: 24px;

      :deep(table) {
        border: 1px $color-white-4 solid;
      }

      :deep(.ant-table-container) {
        border-radius: 8px !important;
        overflow: hidden;
      }

      :deep(.ant-table-thead) {
        .ant-table-cell {
          background: $color-black;
          // border-radius: 0 !important;
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

    .tag-supplier-active {
      color: $color-green-3;
    }

    .tag-supplier-inactive {
      color: $color-error-medium;
    }

    .progress-text {
      font-weight: 700;

      &-complete {
        color: $color-green-4;
      }

      &-incomplete {
        color: $color-error-medium;
      }
    }

    .assignment-supplier-button {
      display: flex;
      align-items: center;
      font-size: 16px !important;
      height: 50px;
      width: 210px;
      background: $color-white;
      color: $color-black;
      border: 1px solid $color-black;
      box-shadow: none !important;

      &:hover {
        background: $color-white-2;
        border: 1px solid $color-black;
      }
    }
  }

  .container-actions {
    display: flex;
    align-items: center;
    justify-content: center;

    .icon-wrapper {
      width: 25px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .btn-icon {
      width: 19px;
      height: 19px;
      color: $color-blue;
    }
  }
</style>
