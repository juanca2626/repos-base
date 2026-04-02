<template>
  <a-row
    type="flex"
    justify="start"
    align="middle"
    class="bg-light header-bar"
    style="gap: 15px"
    v-if="template"
  >
    <a-col>
      <h1 class="page-title">{{ template.name }}</h1>
      <p class="mb-0 text-uppercase">
        {{ template.newType }} | {{ template.duration }} |
        {{ parseFloat(template.percentOpe).toFixed(2) }}% OPE
      </p>
    </a-col>
    <a-col>
      <a-space>
        <a-dropdown>
          <template #overlay>
            <a-menu>
              <a-menu-item @click="handleEdit(template)"> <edit-outlined /> Editar </a-menu-item>
              <a-menu-item @click="handleDepartures(template)">
                <calendar-outlined /> Salidas
              </a-menu-item>
              <a-menu-item @click="handleCostPAX(template)">
                <dollar-outlined /> Costos por PAX
              </a-menu-item>
              <a-menu-item @click="handleServices(template)">
                <calendar-outlined /> Calendario
              </a-menu-item>
            </a-menu>
          </template>
          <a-button type="dashed">
            <SettingFilled />
          </a-button>
        </a-dropdown>
      </a-space>
    </a-col>
  </a-row>
  <div class="content-page">
    <a-button type="primary" size="large" :disabled="isLoading" @click="handleAdd" class="mb-3">
      <PlusOutlined /> Agregar Servicio
    </a-button>
    <a-spin :spinning="isLoading" size="small">
      <template v-if="services.length > 0">
        <tree-view-component
          :data="services"
          @data-updated="handleDataUpdate"
          @edit="handleEdit"
          @save="handleSave"
          @delete="handleDelete"
          @show-modal="showModalProviderScaling"
          @view="handleView"
        />
      </template>
      <template v-else>
        <a-empty :description="`No hay datos para mostrar`" />
      </template>
    </a-spin>
  </div>

  <create-service-modal
    :visible="showModal"
    :locked="service.locked"
    @handleOk="handleOk"
    @handleCancel="handleCancel"
  ></create-service-modal>

  <a-modal
    v-model:visible="showModalProvider"
    width="600px"
    title="CUADRO DE PROPORCIONALIDAD"
    :confirm-loading="isLoading"
    @ok="handleProvidersOk"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
  >
    <div class="header-info-row">
      <div class="info-block">
        <div class="info-label"><b>Servicio:</b></div>
        <div class="info-value">{{ service.name }}</div>
      </div>
      <div class="info-block">
        <div class="info-label"><b>Tarifa/día:</b></div>
        <div class="info-value">USD {{ service.pricing[0].value.toFixed(2) }}</div>
      </div>
      <div class="info-block">
        <div class="info-label"><b>Total:</b></div>
        <div class="info-value total-value">USD {{ service?.price_total?.toFixed(2) }}</div>
      </div>
    </div>
    <div class="mb-3">
      <a-button
        type="primary"
        size="large"
        class="text-center"
        style="width: 100%"
        @click="handleAddPax"
      >
        + PAX
      </a-button>
    </div>

    <a-table
      :columns="columns"
      :data-source="calculatedProviderScaling"
      :pagination="false"
      size="middle"
      :showHeader="true"
      :bordered="true"
    >
      <template #bodyCell="{ column, record, index }">
        <template v-if="column.key === 'providers'">
          <a-input
            :value="record.providers"
            @change="handleProviderChange(index, $event.target.value)"
            type="number"
            :min="1"
            class="guide-input-number"
          />
        </template>
        <template v-else-if="column.key === 'action'">
          <a-button type="text" danger @click="handleRemovePax(index)">
            <span style="font-size: 1.25rem; font-weight: bold; line-height: 1">×</span>
          </a-button>
        </template>
      </template>
    </a-table>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, onMounted, createVNode, computed } from 'vue';
  import { useTemplates, useCategories, useConfiguration } from '@/composables/adventure';
  import { useRoute, useRouter } from 'vue-router';
  import TreeViewComponent from '../global/TreeViewComponent.vue';
  import {
    ExclamationCircleOutlined,
    PlusOutlined,
    SettingFilled,
    EditOutlined,
    CalendarOutlined,
    DollarOutlined,
  } from '@ant-design/icons-vue';
  import CreateServiceModal from './components/CreateServiceModal.vue';
  import { Modal, notification } from 'ant-design-vue';

  const router = useRouter();
  const route = useRoute();

  const {
    isLoading,
    template,
    service,
    services,
    fetchTemplate,
    fetchTemplateServicesGrouped,
    saveService,
    updateService,
    deleteService,
    updateProviders,
    error,
  } = useTemplates();

  const { categories, fetchCategories } = useCategories();
  const { configuration, fetchConfiguration } = useConfiguration();

  const showModal = ref(false);
  const exchange_rate = ref(1);

  const columns = computed(() => [
    {
      title: 'PAX',
      dataIndex: 'pax',
      key: 'pax',
      width: 60,
      align: 'center',
    },
    {
      title: '# GUÍA',
      dataIndex: 'providers',
      key: 'providers',
      align: 'center',
      width: 100,
    },
    {
      title: 'TOTAL USD',
      dataIndex: 'total',
      key: 'total',
      align: 'center',
      width: 100,
    },
    {
      title: 'COSTO/PAX USD',
      dataIndex: 'total_pax',
      key: 'total_pax',
      align: 'center',
      width: 120,
    },
    {
      title: '', // Columna de acción (eliminar)
      key: 'action',
      width: 40,
      align: 'center',
      className: 'ant-table-header-red ant-table-cell-action',
    },
  ]);

  onMounted(async () => {
    const id = route.params.id;

    await fetchConfiguration();

    if (!template.value._id || template.value._id !== id) {
      await fetchTemplate(id);
    }

    if (categories.value.length === 0) {
      await fetchCategories();
    }

    await fetchTemplateServicesGrouped(template.value._id);

    exchange_rate.value = parseFloat(configuration.value?.data?.value ?? 1);
  });

  const handleDataUpdate = (data: any) => {
    console.log('Data updated', data);
  };

  const handleAdd = () => {
    service.value = {
      categoryId: '',
      name: '',
      type: 'costPerPerson',
      costPerPerson: 0,
      ratePerDay: 0,
      provider: '',
      paymentType: 'credit',
      currency: 'USD',
      position: 0,
      isExtra: false,
      isProgrammable: false,
      isTicket: false,
      multiProviders: false,
      user: '',
      allDays: false,
      days: [1],
      locked: false,
      templateId: template.value._id,
      pricing: [
        {
          pax: 0,
          value: 0,
        },
      ],
    };

    showModal.value = true;
  };

  const handleOk = async () => {
    if (service.value._id) {
      await updateService(service.value._id);
    } else {
      if (service.value.type === 'costPerPerson') {
        service.value.pricing = [
          {
            pax: 1,
            value: service.value.costPerPerson,
          },
        ];
      }

      if (service.value.type === 'ratePerDay') {
        service.value.pricing = [
          {
            pax: 1,
            value: service.value.ratePerDay,
          },
        ];
      }

      await saveService();
    }

    if (!error.value) {
      await fetchTemplateServicesGrouped(template.value._id);
      showModal.value = false;
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleCancel = () => {
    showModal.value = false;
    showModalProvider.value = false;
  };

  const handleEdit = (_service: any) => {
    const data = {
      ..._service,
      costPerPerson: parseFloat(_service.pricing[0].value),
      ratePerDay: parseFloat(_service.pricing[0].value),
      days: _service.days.map((day: any) => day.toString()),
      providers: _service.providers.map((provider: any) => provider.code).join(','),
      // locked: true,
    };

    service.value = JSON.parse(JSON.stringify(data));

    console.log(service.value.providers);
    showModal.value = true;
  };

  const handleCostPAX = (_template: any) => {
    template.value = JSON.parse(JSON.stringify(_template));
    router.push({
      name: 'adventure-template-cash',
      params: { id: _template._id },
    });
  };

  const handleView = (_service: any) => {
    const data = {
      ..._service,
      costPerPerson: parseFloat(_service.pricing[0].value),
      ratePerDay: parseFloat(_service.pricing[0].value),
      days: _service.days.map((day: any) => day.toString()),
      locked: true,
    };

    service.value = JSON.parse(JSON.stringify(data));
    showModal.value = true;

    console.log(service.value);
  };

  const handleSave = (_service: any) => {
    console.log('Save', _service);
  };

  const handleDelete = (_service: any) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar el servicio?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá recuperar la información.',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await deleteService(_service._id);
        if (!error.value) {
          await fetchTemplateServicesGrouped(template.value._id);
        }
      },
      onCancel() {
        console.log('Cancel');
      },
    });
  };

  const showModalProvider = ref(false);

  const calculatedProviderScaling = computed(() => {
    // Si providerScaling no existe o no es un array, devolvemos un array vacío.
    if (!service.value.providerScaling || !Array.isArray(service.value.providerScaling)) {
      return [];
    }

    return service.value.providerScaling.map((item: any) => {
      const providers = parseFloat(item.providers) || 1;
      const pax = parseFloat(item.pax) || 1;
      const total = providers * service.value.price_total;
      const totalPax = total / pax;

      return {
        ...item,
        total: total.toFixed(2),
        // Aseguramos que el resultado es válido antes de llamar toFixed
        total_pax: pax !== 0 ? totalPax.toFixed(2) : '0.00',
      };
    });
  });

  const showModalProviderScaling = (_service: any) => {
    const newService = {
      ..._service,
    };

    if (_service.type === 'costPerPerson' || _service.type === 'ratePerDay') {
      newService.price_total = _service.pricing[0].value * _service.days.length;
    }

    service.value = JSON.parse(JSON.stringify(newService));
    showModalProvider.value = true;
  };

  const handleRemovePax = (key: any) => {
    if (service.value.providerScaling) {
      service.value.providerScaling = service.value.providerScaling.filter(
        (_, index: number) => index !== key
      );
    }
  };

  const handleProviderChange = (index: number, value: number) => {
    service.value.providerScaling[index].providers = value;
  };

  const handleAddPax = () => {
    const maxPax = service.value.providerScaling.at(-1)?.pax || 1;

    service.value.providerScaling.push({
      pax: maxPax + 1,
      providers: 1,
    });
  };

  const handleProvidersOk = async () => {
    await updateProviders(service.value._id);
    if (!error.value) {
      await fetchTemplateServicesGrouped(template.value._id);
    }
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';

  .header-info-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    font-size: 0.875rem; /* text-sm */
    margin-bottom: 16px;
    padding-bottom: 8px;
    border-bottom: 1px solid #ccc;
  }

  .info-block {
    flex: 1;
    text-align: center;
    padding: 0 8px;
  }

  .info-label {
    color: #6b7280; /* gray-500 */
    font-weight: 500;
  }

  .info-value {
    color: #1f2937; /* gray-900 */
  }

  .total-value {
    color: #10b981; /* green-600 */
  }
</style>
