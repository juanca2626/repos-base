<template>
  <div class="table-negotiation-services">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="id"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <a-button type="link" class="btn-option-link" @click="handleDestroy(record.id)">
            <font-awesome-icon :icon="['far', 'trash-can']" :style="{ height: '20px' }" />
          </a-button>

          <a-button type="link" class="btn-option-link" @click="handleEdit(record)">
            <font-awesome-icon :icon="['far', 'pen-to-square']" :style="{ height: '20px' }" />
          </a-button>
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
  import { useContactList } from '@/modules/negotiations/supplier/register/configuration-module/composables/contacts/useContactList';

  const {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    handleEdit,
    contextHolder,
    handleDestroy,
  } = useContactList();
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
