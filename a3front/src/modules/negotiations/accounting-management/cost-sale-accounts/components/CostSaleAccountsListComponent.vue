<template>
  <a-typography-text type="secondary" class="mb-2">
    Listado de cuentas costo y venta
  </a-typography-text>
  <a-table
    :columns="columns"
    :data-source="data"
    :pagination="false"
    :loading="isLoading"
    @change="handleTableChange"
    row-key="id"
  >
    <template #headerCell="{ column }">
      <span
        v-if="
          ['user', 'classification', 'cost_account', 'sale_account', 'period', 'options'].includes(
            column.key
          )
        "
        style="color: #ffffff"
      >
        {{ column.title }}
      </span>
    </template>
    <template #bodyCell="{ column, record }">
      <div v-if="column.key === 'user'">
        <strong class="font-bold">ADMIN</strong>
        <p class="font-bold">{{ record.created_at }}</p>
      </div>
      <p v-else-if="column.key === 'classification'">
        {{ record.service_classification.name }}
      </p>
      <p v-else-if="column.key === 'cost_account'">{{ record.cost_account }}</p>
      <p v-else-if="column.key === 'sale_account'">{{ record.sale_account }}</p>
      <p v-else-if="column.key === 'period'">{{ record.date_from }} - {{ record.date_to }}</p>
      <template v-else-if="column.key === 'options'">
        <Can :I="PermissionActionEnum.DELETE" :a="ModulePermissionEnum.COST_SALE_ACCOUNTS">
          <a-button type="link" class="btn-option-link" @click="showPromiseConfirm(record.id)">
            <font-awesome-icon :icon="['far', 'trash-can']" />
          </a-button>
        </Can>
        <Can :I="PermissionActionEnum.UPDATE" :a="ModulePermissionEnum.COST_SALE_ACCOUNTS">
          <a-button type="link" class="btn-option-link" @click="editSettingIgv(record)">
            <font-awesome-icon :icon="['far', 'pen-to-square']" />
          </a-button>
        </Can>
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
  <contextHolder />
</template>
<script setup lang="ts">
  import { defineProps } from 'vue';
  import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/cost-sale-accounts/interfaces/filters-inputs.interface';
  import { useCostSaleAccountsList } from '@/modules/negotiations/accounting-management/cost-sale-accounts/composables/useCostSaleAccountsList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const props = defineProps<{ filters: FiltersInputsInterface }>();

  const {
    columns,
    data,
    pagination,
    isLoading,
    contextHolder,
    editSettingIgv,
    handleTableChange,
    showPromiseConfirm,
    onChange,
  } = useCostSaleAccountsList(props);
</script>
<style scoped lang="scss">
  .btn-option-link {
    color: #2f353a;
    font-size: 18px;
    font-weight: 600;
  }
</style>
