<template>
  <div class="module-negotiations custom-list-table-borderless mt-3">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'statusDescription'">
          <a-tag class="w-80" :class="record.status ? 'success-light' : 'disabled-light'">
            {{ record.statusDescription }}
          </a-tag>
        </template>
        <template v-else-if="column.key === 'isTrunk'">
          <a-switch v-model:checked="record.isTrunk" @change="handleChangeIsTrunk(record)" />
        </template>
        <template v-else-if="column.key === 'action'">
          <template v-if="canDelete">
            <a-button type="link" class="btn-option-link" @click="handleDestroy(record.id)">
              <font-awesome-icon :icon="['far', 'trash-can']" :style="{ height: '20px' }" />
            </a-button>
          </template>
          <template v-if="canUpdate">
            <a-button type="link" class="btn-option-link" @click="handleEdit(record)">
              <font-awesome-icon :icon="['far', 'pen-to-square']" :style="{ height: '20px' }" />
            </a-button>
          </template>
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
  <contextHolder />
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { useTypeUnitList } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitList';
  import { useTypeUnitConfiguratorPermission } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfiguratorPermission';

  const { canUpdate, canDelete } = useTypeUnitConfiguratorPermission();

  const {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    handleChangeIsTrunk,
    contextHolder,
    handleDestroy,
    handleEdit,
  } = useTypeUnitList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .btn-option-link {
    color: $color-black;
    font-size: 18px;
    font-weight: 600;
    display: inline-flex;
  }

  .btn-option-link .anticon {
    line-height: inherit;
    display: flex;
    align-items: center;
  }
</style>
