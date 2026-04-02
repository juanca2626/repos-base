<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Servicios Extra</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-row type="flex" justify="space-between" align="middle">
          <a-col flex="auto">
            <a-form :model="filters" layout="vertical">
              <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Tipo:" name="type" class="mb-0">
                    <a-select
                      v-model:value="filters.type"
                      placeholder="Seleccione un tipo"
                      :show-search="false"
                      :allow-clear="false"
                      :options="types"
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
                  <a-button type="primary" :disabled="isLoading" @click="fetchExtraServices">
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
          <a-col flex="none">
            <a-button type="primary" size="large" :disabled="isLoading" @click="handleAdd">
              <PlusOutlined /> Crear Servicio Extra
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <backend-table-component
      ref="tableRef"
      :items="extraServices"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      @change="handleChange"
    >
      <template #name="{ record }">
        <span class="text-uppercase"
          ><b>{{ record.name }}</b></span
        >
        <span class="d-block">
          <template v-if="record.type === 'range'">
            <i>RANGOS</i>
          </template>
          <template v-else-if="record.type === 'costPerPerson'">
            <i>COSTO POR PERSONA</i>
          </template>
          <template v-else>
            <i>TARIFA POR DÍA</i>
          </template>
        </span>
      </template>
      <template #paymentType="{ record }">
        <a-tooltip
          :title="record.paymentType === 'credit' ? 'COBRO AL CRÉDITO' : 'COBRO AL CONTADO'"
        >
          <span style="font-size: 1rem">
            <credit-card-outlined v-if="record.paymentType === 'credit'" />
            <dollar-outlined v-else />
          </span>
        </a-tooltip>
      </template>
      <template #pricing="{ record }">
        <a-row type="flex" justify="center" align="middle" style="gap: 7px">
          <template v-for="p in record.pricing" :key="p.pax">
            <a-col>
              <div class="bg-light p-2" style="border: 1px dashed #ddd; border-radius: 6px">
                <p class="mb-0" style="line-height: 12px" v-if="record.type === 'range'">
                  <small
                    ><b>HASTA {{ p.pax }} PAX:</b></small
                  >
                </p>
                <p
                  class="mb-0"
                  style="line-height: 12px"
                  v-else-if="record.type === 'costPerPerson'"
                >
                  <small><b>COSTO/PAX:</b></small>
                </p>
                <p class="mb-0" style="line-height: 12px" v-else>
                  <small><b>TARIFA/DÍA:</b></small>
                </p>
                <p class="mb-0 text-center" style="line-height: 12px">
                  <small>{{ record.currency }} {{ p.value.toFixed(2) }}</small>
                </p>
              </div>
            </a-col>
          </template>
        </a-row>
      </template>

      <template #actions="{ record }">
        <a-space>
          <a-dropdown>
            <template #overlay>
              <a-menu>
                <a-menu-item @click="handleEdit(record)"> <EditOutlined /> Editar</a-menu-item>
                <a-menu-item @click="handleDelete(record)">
                  <DeleteOutlined /> Eliminar</a-menu-item
                >
              </a-menu>
            </template>
            <a-button type="dashed">
              <SettingFilled />
            </a-button>
          </a-dropdown>
        </a-space>
      </template>
    </backend-table-component>
  </div>

  <create-service-modal
    :visible="showModal"
    :template="false"
    @handleOk="handleOk"
    @handleCancel="handleCancel"
  />
</template>

<script setup lang="ts">
  import { ref, onMounted, createVNode } from 'vue';
  import { useExtraServices, useTemplates } from '@/composables/adventure';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import CreateServiceModal from '@/components/adventure/components/CreateServiceModal.vue';
  import {
    DeleteOutlined,
    EditOutlined,
    ExclamationCircleOutlined,
    PlusOutlined,
    ReloadOutlined,
    SearchOutlined,
    SettingFilled,
    CreditCardOutlined,
    DollarOutlined,
  } from '@ant-design/icons-vue';
  import { Modal, notification } from 'ant-design-vue';

  const showModal = ref(false);

  const {
    filters,
    types,
    pagination,
    isLoading,
    extraServices,
    fetchExtraServices,
    deleteExtraService,
    updateExtraService,
    saveExtraService,
    extraService,
    error,
  } = useExtraServices();

  const { service } = useTemplates();

  const columns = [
    {
      title: 'Descripción',
      dataIndex: 'name',
      key: 'name',
      isSlot: true,
    },
    {
      title: 'Categoría',
      dataIndex: 'categoryName',
      key: 'categoryName',
      align: 'center',
    },
    {
      title: 'Entrada',
      dataIndex: 'entrance',
      key: 'entrance',
      align: 'center',
      customRender: function () {
        return '-';
      },
    },
    {
      title: 'Prop.',
      dataIndex: 'prop',
      key: 'prop',
      align: 'center',
      customRender: function () {
        return '-';
      },
    },
    {
      title: 'Cobro',
      dataIndex: 'paymentType',
      key: 'paymentType',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Valores',
      dataIndex: 'pricing',
      key: 'pricing',
      isSlot: true,
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: true,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  const clearFilters = () => {
    filters.value = {
      type: '',
      term: '',
    };
  };

  const tableRef = ref();

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;
    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);
    await fetchExtraServices();
  };

  onMounted(async () => {
    await fetchExtraServices();
  });

  const handleAdd = () => {
    service.value = {
      service: '',
      paxs: 0,
      days: [],
    };
    showModal.value = true;
  };

  const handleEdit = (record: any) => {
    console.log(record);
    service.value = {
      ...record,
      type: record.type.replace('cost', 'rate'),
      costPerPerson: record.pricing[0].value,
      ratePerDay: record.pricing[0].value,
    };
    showModal.value = true;
  };

  const handleCancel = () => {
    showModal.value = false;
  };

  const handleOk = async (_service: any) => {
    const id = _service._id ?? '';
    extraService.value = _service;

    if (id) {
      await updateExtraService(id);
    } else {
      await saveExtraService();
    }

    if (!error.value) {
      await fetchExtraServices();
      showModal.value = false;
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleDelete = (record: any) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar el servicio extra?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá recuperar la información.',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await deleteExtraService(record._id);
        if (!error.value) {
          await fetchExtraServices();
        }
      },
      onCancel() {
        console.log('Cancel');
      },
    });
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
