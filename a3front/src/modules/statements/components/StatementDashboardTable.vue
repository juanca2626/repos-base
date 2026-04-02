<template>
  <div class="statement-dashboard-table">
    <div class="search-options">
      <a-space wrap :size="30">
        <a-typography-text strong>Opciones de búsqueda:</a-typography-text>
        <a-radio-group v-model:value="optionSearchSelected" :options="plainOptions"></a-radio-group>
      </a-space>
      <a-tag class="right" :style="{ backgroundColor: '#ffea61', border: 'none' }">
        Total Seleccionado: USD$ {{ statementStore.totalFinal }}
      </a-tag>
    </div>
    <BaseTable
      :config="{ columns }"
      :total="statementStore.getTotal"
      :currentPage="statementStore.getCurrentPage"
      :defaultPerPage="statementStore.getDefaultPerPage"
      :perPage="statementStore.getPerPage"
      :isLoading="statementStore.isLoading"
      :data="statementStore.getStatements"
      :searchOption="optionSearchSelected"
      @onChange="handleChange"
      @onShowSizeChange="handleShowSizeChange"
    />
    <!--    @onChange="handleChange"-->
    <!--    @onShowSizeChange="handleShowSizeChange"-->
    <!--    @onFilter="handleFilter"-->
    <!--    @onFilterBy="handleFilterBy"-->
    <!--    @onRefresh="handleRefresh"-->
    <!--    @onRefreshCache="handleRefreshCache"-->
  </div>
</template>

<script setup>
  import BaseTable from '@/modules/statements/components/BaseTable.vue';
  import { ref } from 'vue';
  import { useStatementsStore } from '@/modules/statements/stores/index.js';
  const optionSearchSelected = ref('op1'); // Valor inicial, puede ser 'op1' u 'op2'
  const statementStore = useStatementsStore();
  const isShowSizeChange = ref(false);
  const currentPageTemp = ref(1);

  const handleChange = async ({
    currentPage,
    perPage,
    filter,
    clientCode,
    dateFrom,
    dateTo,
    searchOption,
  }) => {
    if (isShowSizeChange.value) {
      isShowSizeChange.value = false;
      return;
    }
    currentPageTemp.value = currentPage;
    await statementStore.changePage({
      currentPage,
      perPage,
      filter,
      clientCode,
      dateFrom,
      dateTo,
      searchOption,
    });
  };
  const handleShowSizeChange = async ({
    currentPage,
    perPage: size,
    filter,
    clientCode,
    dateFrom,
    dateTo,
    searchOption,
  }) => {
    isShowSizeChange.value = true;
    statementStore.perPage = size;
    currentPage = 1;
    await statementStore.fetchAll({
      currentPage,
      perPage: size,
      filter,
      clientCode,
      dateFrom,
      dateTo,
      searchOption,
    });
  };
  const columns = [
    { title: 'N°', dataIndex: 'index', key: 'index', width: 50 },
    { title: '', dataIndex: 'select', key: 'select', width: 50 }, // Segunda columna (Checkbox)
    { title: 'File', dataIndex: 'file', key: 'file' },
    { title: 'PAX/GROUP NAME', dataIndex: 'groupName', key: 'groupName' },
    { title: '#PAX', dataIndex: 'paxCount', key: 'paxCount' },
    {
      title: 'Fec.Ing',
      dataIndex: 'entryDate',
      key: 'entryDate',
      render: (text) => formatDate(text),
    },
    {
      title: 'Fec.Sal',
      dataIndex: 'exitDate',
      key: 'exitDate',
      render: (text) => formatDate(text),
    },
    {
      title: 'Fecha Límite de Pago',
      dataIndex: 'paymentDeadline',
      key: 'paymentDeadline',
      render: (text) => formatDate(text),
    },
    { title: 'Importe', dataIndex: 'amount', key: 'amount', render: (text) => `USD ${text}` },
  ];
  // const optionSearchSelected = ref('op1');
  // const opc_2 = ref(null);
  //const selectedSearchOption = ref('total');
  // watch([opc_1, opc_2], ([newOpc1, newOpc2]) => {
  //   // Si ninguno tiene valor, usamos "op1" (que se convertirá en "total")
  //   const value = newOpc1 || newOpc2 || 'op1';
  //   selectedSearchOption.value =
  //     value === 'op1' ? 'total' : value === 'op2' ? 'pendiente' : 'total';
  //   console.log('Nuevo searchOption:', selectedSearchOption.value);
  // });
  const plainOptions = [
    { label: 'Total facturación', value: 'op1' },
    { label: 'Pendiente de pago', value: 'op2' },
  ];
</script>
<style scope>
  .statement-dashboard-table {
    margin-top: 2rem;
    padding-left: 0;
    padding-right: 0;
  }
  .right {
    float: right;
  }
  .search-options {
    margin-left: 1.1cm;
    margin-right: 1cm;
    margin-bottom: 1cm;
  }
</style>
