<template>
  <div class="mt-1">
    <total-records-added-component :total="pagination.total" />

    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      :loading="isLoading"
      row-key="id"
      class="custom-supplier-table-list"
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'action'">
          <div class="container-actions">
            <div class="icon-wrapper">
              <font-awesome-icon :icon="['far', 'pen-to-square']" class="btn-icon" />
            </div>
            <div class="icon-wrapper">
              <icon-ban :height="24" :width="24" color="#1284ED" />
            </div>
            <!-- <div class="icon-wrapper">
              <font-awesome-icon :icon="['far', 'clone']" class="btn-icon" />
            </div> -->
          </div>
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
  import TotalRecordsAddedComponent from '@/modules/negotiations/suppliers/components/partials/total-records-added-component.vue';
  import IconBan from '@/modules/negotiations/suppliers/icons/icon-ban.vue';
  import { useLocalOperatorList } from '@/modules/negotiations/suppliers/local-operators/composables/local-operator-list.composable';

  defineOptions({
    name: 'LocalOperatorListComponent',
  });

  const { data, columns, pagination, isLoading, onChange } = useLocalOperatorList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierList.scss';

  .container-actions {
    display: flex;
    align-items: center;
    justify-content: center;

    .icon-wrapper {
      width: 24px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }

    .btn-icon {
      width: 19px;
      height: 19px;
      color: #1284ed;
    }
  }
</style>
