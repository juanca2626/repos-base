<template>
  <a-typography-text type="secondary" class="mb-2">
    Listado de gastos financieros
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
        v-if="['user', 'type_amount', 'amount_value', 'period', 'options'].includes(column.key)"
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
      <div v-else-if="column.key === 'type_amount'">
        <p v-show="record.type_amount === 'AMOUNT'">Importe</p>
        <p v-show="record.type_amount === 'PERCENTAGE'">Porcentaje</p>
      </div>
      <p v-else-if="column.key === 'amount_value'">{{ record.amount_value }}</p>
      <p v-else-if="column.key === 'period'">{{ record.date_from }} - {{ record.date_to }}</p>
      <template v-else-if="column.key === 'options'">
        <Can :I="PermissionActionEnum.DELETE" :a="ModulePermissionEnum.FINANCIAL_EXPENSES">
          <a-button type="link" @click="showPromiseConfirm(record.id)" class="btn-option-link">
            <font-awesome-icon :icon="['far', 'trash-can']" />
          </a-button>
        </Can>
        <Can :I="PermissionActionEnum.UPDATE" :a="ModulePermissionEnum.FINANCIAL_EXPENSES">
          <a-button type="link" @click="editSettingIgv(record)" class="btn-option-link">
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

<script lang="ts" setup>
  import { defineProps } from 'vue';
  import type { FilterDatesInterface } from '@/modules/negotiations/interfaces/filter-dates.interface';
  import { useFinancialExpensesList } from '@/modules/negotiations/accounting-management/financial-expenses/composables/useFinancialExpensesList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const props = defineProps<{ filters: FilterDatesInterface }>();

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
  } = useFinancialExpensesList(props);
</script>

<style scoped lang="scss">
  .btn-option-link {
    color: #2f353a;
    font-size: 18px;
    font-weight: 600;
  }
</style>
