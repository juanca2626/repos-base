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
          <div class="supplier-common-container-actions">
            <div class="icon-wrapper">
              <font-awesome-icon
                @click="handleEdit(record.id)"
                :icon="['far', 'pen-to-square']"
                class="btn-icon"
              />
            </div>
            <div class="icon-wrapper" :class="{ disabled: !isActive(record.status) }">
              <icon-ban
                :height="24"
                :width="24"
                color="#1284ED"
                @click="handleInactivate(record)"
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
      :open="isOpenDrawer"
      :businessName="supplierListRow?.businessName || ''"
      @close="handleCloseDrawer"
      @confirm="onInactivateSupplier"
    />
  </div>
</template>

<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import TotalRecordsAddedComponent from '@/modules/negotiations/suppliers/components/partials/total-records-added-component.vue';
  import ConfirmInactiveDrawerComponent from '@/modules/negotiations/suppliers/components/confirm-inactive-drawer-component.vue';
  import IconBan from '@/modules/negotiations/suppliers/icons/icon-ban.vue';
  import { useWaterTransportList } from '@/modules/negotiations/suppliers/water-transports/composables/water-transport-list.composable';

  const {
    data,
    columns,
    pagination,
    isLoading,
    isOpenDrawer,
    supplierListRow,
    onChange,
    handleCloseDrawer,
    handleEdit,
    handleInactivate,
    onInactivateSupplier,
    isActive,
  } = useWaterTransportList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierList.scss';
</style>
