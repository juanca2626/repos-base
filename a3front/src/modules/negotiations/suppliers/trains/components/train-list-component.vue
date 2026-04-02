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
              <font-awesome-icon
                @click="handleEdit(record.id)"
                :icon="['far', 'pen-to-square']"
                class="btn-icon"
              />
            </div>
            <div class="icon-wrapper" :class="{ disabled: record.status === 'INACTIVE' }">
              <icon-ban
                :height="24"
                :width="24"
                color="#1284ED"
                @click="record.status !== 'inactive' && handleInactive(record)"
              />
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

    <ConfirmInactiveDrawerComponent
      :open="drawer.isOpen"
      :businessName="infoInactive?.businessName || ''"
      @close="handleClose"
      @confirm="handleConfirmedInactive"
    />
  </div>
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import TotalRecordsAddedComponent from '@/modules/negotiations/suppliers/components/partials/total-records-added-component.vue';
  import IconBan from '@/modules/negotiations/suppliers/icons/icon-ban.vue';
  import ConfirmInactiveDrawerComponent from '../../components/confirm-inactive-drawer-component.vue';
  import { useTrainList } from '@/modules/negotiations/suppliers/trains/composables/train-list.composable';

  const {
    data,
    columns,
    pagination,
    isLoading,
    infoInactive,
    drawer,
    onChange,
    handleEdit,
    handleInactive,
    handleClose,
    handleConfirmedInactive,
  } = useTrainList();
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

      &.disabled {
        opacity: 0.4;
        cursor: not-allowed;
        pointer-events: none;
      }
    }

    .btn-icon {
      width: 19px;
      height: 19px;
      color: #1284ed;
    }
  }
</style>
