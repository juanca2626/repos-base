<template>
  <div class="files-dashboard-table">
    <BaseTable
      :config="config"
      :total="orderStore.getTotal"
      :currentPage="orderStore.getCurrentPage"
      :defaultPerPage="orderStore.getDefaultPerPage"
      :perPage="orderStore.getPerPage"
      :isLoading="orderStore.isLoading"
      :data="orderStore.getOrders"
      @onChange="handleChange"
      @onShowSizeChange="handleShowSizeChange"
      @onFilter="handleFilter"
      @onFilterBy="handleFilterBy"
      @onRefresh="handleRefresh"
      @onRefreshCache="handleRefreshCache"
    />
  </div>
</template>

<script setup>
  import { onBeforeMount, computed } from 'vue';
  import BaseTable from '@ordercontrol/components/BaseTable_OL.vue';

  import { useOrderStore } from '@ordercontrol/store/order.store';

  import { usePermission } from '@ordercontrol/composables/usePermission';

  const orderStore = useOrderStore();
  const { can } = usePermission();

  const config = computed(() => ({
    columns: [
      { id: 1, title: ``, fieldName: 'checkboxes' },
      { id: 2, title: ``, fieldName: 'lights' },
      { id: 3, title: `Fecha viaje`, fieldName: 'travel_date', isFiltered: true },
      { id: 4, title: 'Última comunicación', fieldName: 'last_communication', isFiltered: true },
      { id: 5, title: `Cliente`, fieldName: 'customer', isFiltered: true },
      { id: 6, title: `EC (Area)`, fieldName: 'executive', isFiltered: true },
      { id: 7, title: `Monto estimado`, fieldName: 'estimated_price', isFiltered: true },
      { id: 8, title: `N° Pedido`, fieldName: 'code', isFiltered: true },
      { id: 9, title: `Tipo de cotización`, fieldName: 'type', isFiltered: true },
      ...(can('mfmyorders', 'changestatus')
        ? [{ id: 10, title: `Estado`, fieldName: 'status', isFiltered: true }]
        : []),
      { id: 11, title: `Opciones`, fieldName: 'options' },
    ],
  }));

  const handleChange = async (payload) => {
    console.log('🚀 ~ handleChange ~ payload:', payload);

    await orderStore.fetchAll(payload);
  };

  const handleShowSizeChange = async ({ size }) => {
    orderStore.perPage = size;
    orderStore.currentPage = 1;
    await orderStore.fetchAll();
  };

  const handleFilter = async ({ form }) => {
    await orderStore.fetchAll({ ...form, currentPage: 1, perPage: orderStore.perPage });
  };

  const handleFilterBy = async ({ filterBy, filterByType }) => {
    await orderStore.sortBy({ filterBy, filterByType });
  };

  const handleRefresh = async () => {
    await orderStore.fetchAll();
  };

  onBeforeMount(async () => {
    await orderStore.fetchAll({
      currentPage: orderStore.getCurrentPage,
      perPage: orderStore.getDefaultPerPage,
      filter: null,
      market: null,
      customer: null,
    });
  });
</script>

<style scope>
  .files-dashboard-table {
    margin-top: 2rem;
    padding-left: 0;
    padding-right: 0;
  }
</style>
