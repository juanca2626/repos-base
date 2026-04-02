<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Manifiesto de Paxs</h1>
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
                  <a-form-item label="Código de Servicio:" name="codes" class="mb-0">
                    <a-input
                      autocomplete="off"
                      v-model:value="filters.codes"
                      placeholder="Ingrese el código del servicio.."
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="3">
                  <a-form-item label="Fec. Inicio:" name="value" class="mb-0">
                    <a-date-picker
                      class="w-100"
                      v-model:value="filters.date_from"
                      :format="dateFormat"
                      value-format="YYYY-MM-DD"
                      placeholder="Seleccione.."
                    />
                  </a-form-item>
                </a-col>
                <a-col :span="3">
                  <a-form-item label="Fec. Fin:" name="value" class="mb-0">
                    <a-date-picker
                      class="w-100"
                      v-model:value="filters.date_to"
                      :format="dateFormat"
                      value-format="YYYY-MM-DD"
                      placeholder="Seleccione.."
                    />
                  </a-form-item>
                </a-col>
                <a-col>
                  <a-button
                    type="primary"
                    :disabled="
                      isLoading ||
                      filters.codes === '' ||
                      filters.date_from === '' ||
                      filters.date_to === ''
                    "
                    @click="fetchManifestos"
                  >
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

    <a-row
      v-if="total_paxs > 0"
      type="flex"
      justify="space-between"
      align="middle"
      class="bg-light header-bar mb-3"
    >
      <a-col>
        <h1 class="page-title">Cantidad de Paxs</h1>
      </a-col>
      <a-col>
        <h1 class="page-title">{{ total_paxs }}</h1>
      </a-col>
    </a-row>

    <backend-table-component
      ref="tableRef"
      :items="manifestos"
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
  import { ref } from 'vue';
  import { useManifestos } from '@/composables/adventure';
  import BackendTableComponent from '../global/BackendTableComponent.vue';
  import { ReloadOutlined, SearchOutlined } from '@ant-design/icons-vue';

  const dateFormat = 'DD/MM/YYYY';
  const { filters, pagination, isLoading, manifestos, fetchManifestos, total_paxs } =
    useManifestos();

  const columns = [
    {
      title: 'FILE',
      dataIndex: 'nroref',
      key: 'nroref',
      align: 'center',
    },
    {
      title: 'Código de Servicio',
      dataIndex: 'codsvs',
      key: 'codsvs',
      align: 'center',
    },
    {
      title: 'Descripción',
      dataIndex: 'descri',
      key: 'descri',
      align: 'center',
    },
    {
      title: 'CNT. Paxs',
      dataIndex: 'canpax',
      key: 'canpax',
      align: 'center',
    },
    {
      title: 'Nombre',
      dataIndex: 'pax_nombre',
      key: 'pax_nombre',
      align: 'center',
    },
    {
      title: 'Entrada',
      dataIndex: 'fecin',
      key: 'fecin',
      align: 'center',
    },
    {
      title: 'Salida',
      dataIndex: 'fecout',
      key: 'fecout',
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: false,
    pagination: false,
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
    await fetchManifestos();
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
