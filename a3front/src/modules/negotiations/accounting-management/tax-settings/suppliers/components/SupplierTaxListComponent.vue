<template>
  <a-typography-text type="secondary" class="mb-2">
    Listado de leyes asignadas a proveedores
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
          [
            'type_law',
            'supplier_sub_classification',
            'status_assignment',
            'period',
            'options',
          ].includes(column.key)
        "
        style="color: #ffffff"
      >
        {{ column.title }}
      </span>
    </template>
    <template #bodyCell="{ column, record }">
      <p v-if="column.key === 'type_law'">{{ record.law.name }}</p>
      <div v-else-if="column.key === 'supplier_sub_classification'">
        <a-tag
          color="#07DF81"
          v-for="(item, index) in record.taxes_supplier_classifications"
          :key="index"
          class="mb-2"
        >
          {{ item.name }}
        </a-tag>
      </div>
      <div v-else-if="column.key === 'status_assignment'">
        <a-tag color="#87d068" v-if="record.status_assignment">Asignado</a-tag>
        <a-tag color="orange" v-if="!record.status_assignment">Por asignar</a-tag>
      </div>
      <p v-else-if="column.key === 'period'">
        {{ record.law.date_from }} - {{ record.law.date_to }}
      </p>
      <template v-else-if="column.key === 'options'">
        <a-dropdown class="dropdown-options-table">
          <template #overlay>
            <a-menu>
              <Can :I="PermissionActionEnum.UPDATE" :a="ModulePermissionEnum.TAX_SUPPLIER_TYPE">
                <a-menu-item key="1" @click="editSettingIgv(record)">
                  <font-awesome-icon :icon="['far', 'pen-to-square']" />
                  Editar
                </a-menu-item>
              </Can>
              <Can
                :I="PermissionActionEnum.COMPLETE_ASSIGNMENT"
                :a="ModulePermissionEnum.TAX_SUPPLIER_TYPE"
              >
                <a-menu-item key="2" @click="goToAssignments(record)">
                  <font-awesome-icon :icon="['fas', 'hand-pointer']" />
                  Completar asignación
                </a-menu-item>
              </Can>
              <Can :I="PermissionActionEnum.DELETE" :a="ModulePermissionEnum.TAX_SUPPLIER_TYPE">
                <a-menu-item key="3" @click="showPromiseConfirm(record.id)">
                  <font-awesome-icon icon="fa-solid fa-trash-alt" />
                  Eliminar
                </a-menu-item>
              </Can>
            </a-menu>
          </template>
          <a-button type="default" style="background-color: #e4e5e6">
            <MenuFoldOutlined />
          </a-button>
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
  <contextHolder />
</template>
<script setup lang="ts">
  import { defineProps } from 'vue';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { MenuFoldOutlined } from '@ant-design/icons-vue';
  import type { FiltersInputsInterface } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/interfaces/filter-inputs.interface';
  import { useSupplierTaxList } from '@/modules/negotiations/accounting-management/tax-settings/suppliers/composables/useSupplierTaxList';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';

  const props = defineProps<{ filtersSupplierTax: FiltersInputsInterface }>();

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
    goToAssignments,
  } = useSupplierTaxList(props);
</script>
<style scoped lang="scss"></style>
