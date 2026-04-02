<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Requerimiento de Efectivo</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-row type="flex" justify="space-between" align="middle">
          <a-col :span="18">
            <a-form :model="filters" layout="vertical">
              <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Estado:" name="value" class="mb-0">
                    <a-select
                      v-model:value="filters.status"
                      placeholder="Seleccione un tipo"
                      :show-search="false"
                      :allow-clear="false"
                      :options="states"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="6">
                  <a-form-item label="Término de búsqueda:" name="value" class="mb-0">
                    <a-input
                      autocomplete="off"
                      v-model:value="filters.term"
                      placeholder="Ingrese algo para buscar.."
                    />
                  </a-form-item>
                </a-col>
                <a-col>
                  <a-button type="primary" :disabled="isLoading" @click="fetchEffective">
                    <SearchOutlined />
                  </a-button>
                </a-col>
                <a-col>
                  <a-button type="dashed" :disabled="isLoading" @click="clearFilters">
                    <ReloadOutlined />
                  </a-button>
                </a-col>
              </a-row>
            </a-form>
          </a-col>
        </a-row>
      </div>
    </div>

    <backend-table-component
      ref="tableRef"
      :items="effective"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      @change="handleChange"
    >
    </backend-table-component>
  </div>
</template>

<script setup lang="ts">
  import { h, ref } from 'vue';
  import { useEffective } from '@/composables/adventure';
  import BackendTableComponent from '../global/BackendTableComponent.vue';
  import { ReloadOutlined, SearchOutlined } from '@ant-design/icons-vue';
  import { Tag as ATag } from 'ant-design-vue';

  const { filters, states, pagination, isLoading, effective, fetchEffective } = useEffective();

  const columns = [
    {
      title: 'Nro. Recibo',
      dataIndex: 'code',
      key: 'code',
      align: 'center',
    },
    {
      title: 'Fecha',
      dataIndex: 'startDate',
      key: 'startDate',
      align: 'center',
    },
    {
      title: 'Paquete',
      dataIndex: 'templateName',
      key: 'templateName',
      align: 'center',
    },
    {
      title: 'Servicio',
      dataIndex: 'serviceCode',
      key: 'serviceCode',
      align: 'center',
    },
    {
      title: 'Tipo',
      dataIndex: 'typeName',
      key: 'typeName',
      align: 'center',
    },
    {
      title: 'Duración',
      dataIndex: 'durationName',
      key: 'durationName',
      align: 'center',
    },
    {
      title: 'File OPE',
      dataIndex: 'opeFile',
      key: 'opeFile',
      align: 'center',
    },
    {
      title: 'File(s) Comerciale(s)',
      dataIndex: 'commercialFiles',
      key: 'commercialFiles',
      align: 'center',
      isComponent: true,
      customRender: (data: any) => {
        if (data && data.length > 0) {
          const vNodes = data.map((value: any) => {
            return h(ATag, { color: 'red' }, value || '-');
          });
          return h('div', {}, vNodes);
        }
        return h('div', null, '-');
      },
    },
    {
      title: 'CNT. PAX',
      dataIndex: 'paxCount',
      key: 'paxCount',
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: false,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  const clearFilters = () => {
    filters.value = {
      status: '',
      term: '',
    };
  };

  const tableRef = ref();

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;
    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);
    await fetchEffective();
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
