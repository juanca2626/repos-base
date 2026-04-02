<template>
  <div class="content-page" v-if="departure._id">
    <a-row type="flex" justify="space-between" align="middle" style="gap: 10px" class="mb-3">
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">Paquete:</p>
        <h4 class="page-title">{{ departure.templateName }}</h4>
      </a-col>
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">Tipo:</p>
        <h4 class="page-title">{{ departure.template.newType }}</h4>
      </a-col>
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">Duración:</p>
        <h4 class="page-title">{{ departure.template.duration }}</h4>
      </a-col>
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">Fecha de Inicio:</p>
        <h4 class="page-title">{{ departure.dateStart }}</h4>
      </a-col>
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">File OPE:</p>
        <h4 class="page-title">{{ departure.opeFile }}</h4>
      </a-col>
      <a-col flex="auto" class="text-center">
        <p class="mb-0 text-uppercase">Cantidad de PAXS:</p>
        <h4 class="page-title">{{ departure.paxs }}</h4>
      </a-col>
    </a-row>
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-form :model="file_filters" layout="vertical">
          <a-row type="flex" justify="space-between" align="middle">
            <a-col flex="auto">
              <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Tipo de cobro:" name="state" class="mb-0">
                    <a-select
                      v-model:value="file_filters.type"
                      placeholder="Seleccione una opción"
                      :show-search="false"
                      :allow-clear="false"
                      :options="file_types"
                      @change="updateFileType"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="4">
                  <a-form-item label="Resumen costos:" name="option" class="mb-0">
                    <a-select
                      v-model:value="file_filters.option"
                      placeholder="Seleccione una opción"
                      :show-search="false"
                      :allow-clear="false"
                      :options="file_options"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
              </a-row>
            </a-col>
            <a-col>
              <a-row type="flex" justify="end" style="gap: 7px">
                <a-col>
                  <a-tooltip title="BAJAR FILE">
                    <a-button @click="fetchData" type="dashed" size="large">
                      <SyncOutlined />
                    </a-button>
                  </a-tooltip>
                </a-col>
                <a-col>
                  <a-button
                    type="primary"
                    size="large"
                    :disabled="templateServicesFilter.length === 0 || departure.isClosed"
                    @click="showModalClose = !showModalClose"
                    :loading="isLoading"
                  >
                    <DownloadOutlined /> Cerrar File
                  </a-button>
                </a-col>
                <a-col>
                  <a-button
                    type="dashed"
                    danger
                    size="large"
                    :disabled="templateServicesFilter.length === 0 || !departure.isClosed"
                    @click="handleDeleteCloseProcess"
                    :loading="isLoading"
                  >
                    <DeleteFilled /> Eliminar proceso "Cierre"
                  </a-button>
                </a-col>
              </a-row>
            </a-col>
          </a-row>
        </a-form>
      </div>
    </div>

    <template v-if="departure.isClosed">
      <a-alert type="warning" show-icon size="small">
        <template #icon>
          <FolderOpenFilled />
        </template>
        <template #description>
          <span class="d-block"
            ><b>File {{ departure.opeFile }}</b> cerrado por <b>{{ departure.closedBy }}</b> el
            <b>{{ departure.closedAt }}</b></span
          >
        </template>
      </a-alert>
    </template>

    <h4 class="section-title mb-3">SERVICIOS:</h4>

    <div class="table-container">
      <backend-table-component
        :items="templateServicesFilter"
        :columns="columns"
        :options="tableOptions"
        size="small"
        :total-items="templateServicesFilter.length"
        :rowClassName="rowClassName"
      >
        <!-- Custom Renders -->
        <template #name="{ record }">
          <div class="d-flex align-items-center" style="gap: 7px">
            <span style="font-size: 1rem">
              <a-tooltip>
                <template #title>
                  <small class="text-uppercase">{{
                    record.paymentType === 'credit' ? 'CREDITO' : 'CONTADO'
                  }}</small>
                </template>
                <CreditCardOutlined v-if="record.paymentType === 'credit'" />
                <DollarOutlined v-else />
              </a-tooltip>
            </span>
            <span style="font-size: 1rem" v-if="record.isProgrammable">
              <a-tooltip>
                <template #title>
                  <small class="text-uppercase">PROGRAMABLE</small>
                </template>
                <ClockCircleOutlined />
              </a-tooltip>
            </span>
            <b>{{ record.name }}</b>
          </div>
          <p>
            {{ record.type === 'range' ? 'RN' : record.type === 'costPerPerson' ? 'CP' : 'TD' }} -
            {{ record.category.name }}
          </p>
        </template>

        <template #codpro="{ record }">
          <div class="d-flex align-items-center" style="flex-direction: column; gap: 10px">
            <template v-if="record.isZeroCostAssistant"> -- </template>
            <template v-else>
              <template v-if="record.isProgrammable">
                <template
                  v-if="
                    !record.programmingInformation ||
                    record.programmingInformation.status === 'UNPROGRAMMED'
                  "
                >
                  <p class="d-flex text-center mb-0" style="gap: 7px">
                    <i
                      >Proveedor pendiente de asignar en
                      <a @click="handleProgramming">PROGRAMACIÓN</a></i
                    >
                  </p>
                </template>
                <template v-else>
                  <template v-if="record.providers && record.providers.length > 0">
                    <a-tag color="red">
                      <b>{{ record.providers[0].code }}</b>
                    </a-tag>
                  </template>
                  <template v-if="record.programmingInformation.status === 'PROGRAMMED'">
                    <div class="d-block">
                      <i>Pendiente: Envío ORDEN DE SERVICIO en PROGRAMACIÓN</i>
                    </div>
                  </template>
                </template>
              </template>
              <template v-else>
                <template v-if="record.providers && record.providers.length > 0">
                  <a-tag
                    color="red"
                    v-for="(provider, p) in record.providers"
                    :key="`provider-${p}`"
                  >
                    <b>{{ provider.code }}</b>
                  </a-tag>
                </template>
                <template
                  v-if="
                    record.paymentType !== 'cash' &&
                    (record.priceTotal > 0 || record.providers.length > 0)
                  "
                >
                  <a-button
                    type="dashed"
                    :danger="record.providers.length === 0"
                    size="small"
                    @click="showModalProvider(record)"
                  >
                    <template v-if="!record.providers || record.providers.length === 0">
                      <small class="text-600">SUBIR PROVEEDOR(ES) <UsergroupAddOutlined /></small>
                    </template>
                    <template v-else>
                      <EditOutlined />
                    </template>
                  </a-button>
                </template>
                <template v-else> - </template>
              </template>
            </template>
          </div>
        </template>

        <template #realCost="{ record }">
          <template v-if="record.isEditable">
            <a-input
              type="number"
              v-model:value="record.realCost"
              :disabled="isLoading"
              style="width: 100%"
              @pressEnter="handleSaveRealCost(record)"
              @blur="handleSaveRealCost(record)"
            />
          </template>
          <template v-else>
            <span>
              $ {{ record.realCost }}
              <EditOutlined
                @click="record.isEditable = true"
                v-if="record.paymentType !== 'cash' && !record.isZeroCostAssistant"
                class="cursor-pointer"
              />
            </span>
          </template>
        </template>

        <template #hide="{ record }">
          <a-tooltip
            :title="
              isDisabled(record)
                ? 'No se puede ocultar si el servicio tiene proveedor y el costo real es mayor a cero'
                : record.isHidden
                  ? 'Mostrar'
                  : 'Ocultar'
            "
          >
            <a-button
              type="dashed"
              :disabled="isDisabled(record)"
              @click="handleHideService(record)"
            >
              <template v-if="!record.isHidden">
                <EyeOutlined />
              </template>
              <template v-else>
                <EyeInvisibleOutlined />
              </template>
            </a-button>
          </a-tooltip>
        </template>

        <template #close="{ record }">
          <a-tooltip title="Bajar Componente">
            <a-button
              type="dashed"
              :disabled="record.isHidden || record.isAccounted || record.isZeroCostAssistant"
              @click="handleConfirmCloseService(record)"
            >
              <template v-if="record.isActive">
                <DownloadOutlined />
              </template>
              <template v-else>
                <DownloadOutlined />
              </template>
            </a-button>
          </a-tooltip>
        </template>

        <!-- Footer personalizado usando el slot footer (debajo de la tabla) -->
        <template #footer v-if="file_filters.option === 'show'">
          <a-table
            :columns="columns"
            :dataSource="[
              {
                key: '1',
                label: 'COSTO/PAX:',
                priceUnit: templateSummary.totalCalculatedCostPerPax,
                paxs: departure.paxs || 0,
                priceTotal: templateSummary.totalCalculatedCostPackage,
                realCost: templateSummary.totalRealCostPackage,
                benefit: templateSummary.totalBenefit,
                benefitPercentage: templateSummary.totalBenefitPercentage,
              },
              {
                key: '2',
                label: `+ ${parseFloat(templateSummary.percentOpe).toFixed(2)}% OPE:`,
                priceUnit: templateSummary.totalCalculatedCostPerPaxWithOpe,
                paxs: departure.paxs || 0,
                priceTotal: templateSummary.totalCalculatedCostPackageWithOpe,
                realCost: templateSummary.totalRealCostPackage,
                benefit: templateSummary.totalBenefitWithOpe,
                benefitPercentage: templateSummary.totalBenefitPercentageWithOpe,
              },
            ]"
            :pagination="false"
            :showHeader="false"
            size="small"
            :bordered="false"
            class="custom-footer-summary"
          >
            <template #bodyCell="{ column, text, record }">
              <template v-if="column.dataIndex === 'priceUnit'">
                <span class="d-block text-uppercase mb-1 text-400">{{ record.label }}</span>
              </template>
              <template v-if="column.dataIndex === 'priceTotal'">
                <span class="d-block text-uppercase mb-1 text-400">COSTO/PAQ</span>
              </template>
              <template v-if="column.dataIndex === 'realCost'">
                <span class="d-block text-uppercase mb-1 text-400">Costo Real</span>
              </template>
              <template v-if="column.dataIndex === 'benefit'">
                <span class="d-block text-uppercase mb-1 text-400">BENEFICIO</span>
              </template>
              <template v-if="column.dataIndex === 'benefitPercentage'">
                <span class="d-block text-uppercase mb-1 text-400">% BENEFICIO</span>
              </template>
              {{ text }}
            </template>
          </a-table>
        </template>
      </backend-table-component>
    </div>
  </div>

  <a-modal
    v-model:visible="showModal"
    :confirm-loading="isLoading"
    title="PROVEEDORES"
    @cancel="cancelModal"
    @ok="handleSaveProvider"
    okText="PROCESAR"
    cancelText="CERRAR"
    style="width: 900px"
  >
    <a-form layout="vertical">
      <a-form-item label="Buscar y Seleccionar Proveedores:" name="provider">
        <a-input
          v-model:value="searchProvider"
          placeholder="Código o Nombre de proveedor"
          size="large"
          autocomplete="off"
          @change="onSearch"
        >
          <template #suffix>
            <LoadingOutlined v-if="isLoading" />
          </template>
        </a-input>
      </a-form-item>
    </a-form>

    <template v-if="guides.length > 0">
      <p class="mt-3 mb-0 text-500" style="font-size: 12px">SELECCIONAR:</p>

      <backend-table-component
        :items="guides"
        :columns="columnGuides"
        :options="tableOptionProviders"
        size="small"
      >
        <template #actions="{ record }">
          <a-tooltip placement="right">
            <template #title>
              <small class="text-uppercase">{{
                !record.isSelected ? 'Seleccionar Proveedor' : 'Remover Proveedor'
              }}</small>
            </template>
            <template v-if="!record.isSelected">
              <a-button type="dashed" danger @click="handleAssignProvider(record)">
                <span style="font-size: 1.25rem; font-weight: bold; line-height: 1">
                  <PlusOutlined />
                </span>
              </a-button>
            </template>
            <template v-else>
              <a-button type="dashed" danger @click="handleRemoveProvider(record)">
                <span style="font-size: 1.25rem; font-weight: bold; line-height: 1">
                  <DeleteOutlined />
                </span>
              </a-button>
            </template>
          </a-tooltip>
        </template>
      </backend-table-component>
    </template>

    <template v-if="providers.length > 0">
      <p class="mt-3 mb-0 text-500" style="font-size: 12px">PROVEEDORES SELECCIONADOS:</p>

      <backend-table-component
        :items="providers"
        :columns="columnProviders"
        :options="tableOptionProviders"
        size="small"
      >
        <template #actions="{ record }">
          <a-tooltip placement="right">
            <template #title>
              <small class="text-uppercase">Remover Proveedor</small>
            </template>
            <a-button type="dashed" danger @click="handleRemoveProvider(record)">
              <span style="font-size: 1.25rem; font-weight: bold; line-height: 1">
                <DeleteOutlined />
              </span>
            </a-button>
          </a-tooltip>
        </template>
      </backend-table-component>
    </template>
  </a-modal>

  <a-modal
    v-model:visible="showModalClose"
    title="BAJADA DE FILE (COMPONENTE)"
    :confirm-loading="isLoading"
    @ok="handleCloseFile"
    @cancel="cancelModal"
    okText="GUARDAR"
    cancelText="CANCELAR"
    width="500px"
  >
    <a-form layout="vertical">
      <a-form-item label="Fecha de bajada:" name="closedAt" class="mb-0">
        <a-date-picker
          class="w-100"
          size="large"
          v-model:value="departure.closedAt"
          :format="dateFormat"
          value-format="DD/MM/YYYY"
          :disabled-date="disabledDate"
          placeholder="Seleccione.."
        />
      </a-form-item>
    </a-form>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, onBeforeMount, computed, createVNode } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useDepartures } from '@/composables/adventure';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import { debounce } from 'lodash';
  import {
    FolderOpenFilled,
    DeleteFilled,
    EditOutlined,
    EyeOutlined,
    DownloadOutlined,
    EyeInvisibleOutlined,
    SyncOutlined,
    UsergroupAddOutlined,
    ExclamationCircleOutlined,
    CreditCardOutlined,
    DollarOutlined,
    ClockCircleOutlined,
    PlusOutlined,
    LoadingOutlined,
    DeleteOutlined,
  } from '@ant-design/icons-vue';
  import { Modal, notification } from 'ant-design-vue';
  import dayjs, { Dayjs } from 'dayjs';

  const dateFormat = 'DD/MM/YYYY';
  const route = useRoute();

  const {
    isLoading,
    departure,
    templateServices,
    fetchDepartureTemplateServices,
    findDeparture,
    file_filters,
    file_options,
    file_types,
    closeFile,
    deleteCloseProcess,
    searchGuides,
    guides,
    hideService,
    closeService,
    updateRealCost,
    error,
    updateTemplateServiceProviders,
    templateSummary,
  } = useDepartures();

  const isDisabled = (record: any) => {
    return record.providers.length > 0 || record.realCost > 0;
  };

  const columnGuides = [
    {
      title: 'Código',
      dataIndex: 'codigo',
      key: 'codigo',
      align: 'center',
    },
    {
      title: 'Tipo',
      dataIndex: 'type',
      key: 'type',
      align: 'center',
    },
    {
      title: 'Descripción',
      dataIndex: 'razon',
      key: 'razon',
      align: 'center',
    },
  ];

  const columnProviders = [
    {
      title: 'Código',
      dataIndex: 'codigo',
      key: 'codigo',
      align: 'center',
    },
    {
      title: 'Tipo',
      dataIndex: 'typeDescription',
      key: 'typeDescription',
      align: 'center',
    },
    {
      title: 'Descripción',
      dataIndex: 'razon',
      key: 'razon',
      align: 'center',
    },
  ];

  const tableOptionProviders = {
    bordered: true,
    rowKey: '_id',
    pagination: false,
    showActions: true,
  };

  const columns = [
    {
      title: 'Fecha',
      dataIndex: 'startDate',
      width: '90px',
      align: 'center',
    },
    {
      title: 'Servicio',
      dataIndex: 'name',
      width: '280px',
      isSlot: true,
      ellipsis: true,
    },
    {
      title: 'Días',
      dataIndex: 'newDays',
      width: '60px',
      align: 'center',
    },
    {
      title: 'Cód. Serv.',
      dataIndex: 'codsvs',
      width: '100px',
      align: 'center',
    },
    {
      title: 'Proveedor',
      dataIndex: 'codpro',
      width: '100px',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Costo/PAX',
      dataIndex: 'priceUnit',
      width: '95px',
      align: 'center',
    },
    {
      title: 'PAXs',
      dataIndex: 'paxs',
      width: '60px',
      align: 'center',
    },
    {
      title: 'Costo/PAQ',
      dataIndex: 'priceTotal',
      width: '95px',
      align: 'center',
    },
    {
      title: 'Costo Real',
      dataIndex: 'realCost',
      width: '110px',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Beneficio',
      dataIndex: 'benefit',
      width: '95px',
      align: 'center',
    },
    {
      title: '% Benef.',
      dataIndex: 'benefitPercentage',
      width: '85px',
      align: 'center',
    },
    {
      title: '',
      dataIndex: 'hide',
      width: '75px',
      align: 'center',
      isSlot: true,
    },
    {
      title: '',
      dataIndex: 'close',
      width: '75px',
      align: 'center',
      isSlot: true,
    },
  ];

  const tableOptions = {
    pagination: false,
    bordered: true,
    rowKey: '_id',
    showActions: false,
  };

  const fetchData = async () => {
    const { id } = route.params;

    if (departure.value._id === '' || departure.value._id !== id) {
      await findDeparture(id);
    }

    await fetchDepartureTemplateServices(id);
  };

  onBeforeMount(async () => {
    await fetchData();
  });

  const templateServicesFilter = computed(() => {
    const type = file_filters.value.type ?? '';
    if (!type) {
      return templateServices.value;
    }
    return templateServices.value.filter((service: any) => service.paymentType === type);
  });

  const showModal = ref(false);
  const showModalClose = ref(false);
  const service = ref({});

  const disabledDate = (current: Dayjs) => {
    return current && current > dayjs().startOf('day').add(1, 'day');
  };

  const showModalProvider = (record: any) => {
    guides.value = [];
    searchProvider.value = '';
    const transformed = record.providers.map((provider: any) => {
      return {
        ...provider,
        codigo: provider.code,
        razon: provider.name,
      };
    });

    providers.value = JSON.parse(JSON.stringify(transformed));
    service.value = JSON.parse(JSON.stringify(record));
    showModal.value = true;
  };

  const cancelModal = () => {
    showModal.value = false;
    showModalClose.value = false;
  };

  const handleSaveProvider = async () => {
    let data = [...(service.value?.providers ?? [])];

    if (guides.value.length > 0) {
      const existingCodes = new Set(data.map((p) => p.code));

      const newProviders = guides.value
        .filter((guide: any) =>
          providers.value.some((provider: any) => provider.code === guide.code)
        )
        .map((guide: any) => ({
          code: guide.code,
          type: guide.type,
          typeDescription: guide.descla,
          name: guide.descri,
          isPermanent: guide.isPermanent,
        }))
        .filter((newP: any) => !existingCodes.has(newP.code));

      data.push(...newProviders);
    }

    await updateTemplateServiceProviders(service.value._id, data);
    await fetchData();
    showModal.value = false;
  };

  const searchProvider = ref('');

  const handleSearchProviders = debounce(async () => {
    await searchGuides(searchProvider.value);

    guides.value.forEach((guide: any) => {
      guide.isSelected = false;
      if (providers.value.some((provider: any) => provider.code === guide.code)) {
        guide.isSelected = true;
      }
    });
  }, 350);

  const providers = ref<string[]>([]);

  const handleAssignProvider = (record: any) => {
    record.isSelected = true;
    providers.value.push(record);
  };

  const handleRemoveProvider = (record: any) => {
    record.isSelected = false;
    providers.value = providers.value.filter((provider: any) => provider.code !== record.code);
    service.value.providers = service.value.providers.filter(
      (provider: any) => provider.code !== record.code
    );
  };

  const handleHideService = async (record: any) => {
    const isHidden = !record.isHidden;
    await hideService(record._id, isHidden);

    if (!error.value) {
      record.isHidden = isHidden;
      notification.success({
        message: 'Éxito',
        description: 'Servicio actualizado correctamente',
      });
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleConfirmCloseService = (record: any) => {
    Modal.confirm({
      title: '¿Seguro de cerrar este servicio?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá reestablecer la información.',
      okText: 'Sí, continuar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await handleCloseService(record);
      },
    });
  };

  const handleCloseService = async (record: any) => {
    const isAccounted = !record.isAccounted;
    await closeService(record._id, isAccounted);

    if (!error.value) {
      record.isAccounted = isAccounted;
      notification.success({
        message: 'Éxito',
        description: 'Servicio actualizado correctamente',
      });
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleCloseFile = async () => {
    if (!departure.value.closedAt) {
      notification.error({
        message: 'Error',
        description: 'Seleccione una fecha de cierre para continuar.',
      });
      return;
    }

    await closeFile();

    if (!error.value) {
      notification.success({
        message: 'Éxito',
        description: 'Proceso realizado correctamente.',
      });
      departure.value.isClosed = true;
      cancelModal();
    } else {
      notification.error({
        message: 'Error',
        description: error.value ?? 'Error al cerrar el FILE.',
      });
    }
  };

  const handleDeleteCloseProcess = async () => {
    await deleteCloseProcess();

    if (!error.value) {
      notification.success({
        message: 'Éxito',
        description: 'Proceso cerrado correctamente',
      });
      departure.value.isClosed = false;
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleSaveRealCost = async (record: any) => {
    await updateRealCost(record._id, record.realCost);

    if (!error.value) {
      record.isEditable = false;
      await fetchData();
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const onSearch = async () => {
    if (searchProvider.value.length > 2) {
      await handleSearchProviders();
    }
  };

  const router = useRouter();

  const handleProgramming = () => {
    const routeData = router.resolve({
      name: 'adventure-departure-programming',
      params: { id: departure.value._id },
    });
    window.open(routeData.href, '_blank');
  };

  const rowClassName = (record: any) => {
    if (record.isHidden) {
      return 'bg-light';
    }
    if (record.realCost > record.priceTotal) {
      return 'bg-danger';
    }
    return 'bg-green';
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';

  /* Custom Footer Summary */
  :deep(.custom-footer-summary .ant-table),
  :deep(.custom-footer-summary .ant-table tr:hover) {
    background-color: #e8e8e8 !important;
    color: #373737 !important;
    padding: 0 !important;
    font-size: 13px;
    font-weight: bold;
  }

  .input-dark {
    background-color: #f0f0f0;
    border-color: #d9d9d9;
  }
  :deep(.ant-input-number-input) {
    text-align: right;
    font-weight: bold;
  }

  .service-cell {
    display: flex;
    align-items: center;
    gap: 10px;
  }

  .service-icon {
    font-size: 18px;
    color: #1890ff;
    flex-shrink: 0;
  }

  .service-info {
    flex: 1;
    min-width: 0;
  }

  .service-title {
    font-weight: 500;
    color: #262626;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .service-subtitle {
    font-size: 11px;
    color: #8c8c8c;
    margin-top: 2px;
  }
</style>
