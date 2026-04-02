<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">
        <template v-if="departure?.opeFile"> FILE: {{ departure.opeFile }} </template>
        <template v-else> Programación </template>
      </h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-form :model="filters" layout="vertical">
          <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
            <a-col :span="4">
              <a-form-item label="Ver servicios:" name="state" class="mb-0">
                <a-select
                  v-model:value="filters.state"
                  placeholder="Seleccione una opción"
                  :show-search="false"
                  :allow-clear="false"
                  :options="states"
                  @change="fetchProgramming"
                >
                </a-select>
              </a-form-item>
            </a-col>
            <a-col :span="4">
              <a-form-item label="Tipo de búsqueda:" name="type" class="mb-0">
                <a-select
                  v-model:value="filters.type"
                  placeholder="Seleccione una opción"
                  :show-search="false"
                  :allow-clear="false"
                  :options="types"
                >
                </a-select>
              </a-form-item>
            </a-col>
            <template v-if="filters.type !== 'ALL'">
              <a-col :span="4" v-if="filters.type !== 'DATE'">
                <a-form-item label="Término de búsqueda:" name="value" class="mb-0">
                  <a-input
                    autocomplete="off"
                    v-model:value="filters.term"
                    :type="filters.type === 'FILE' ? 'number' : 'text'"
                    placeholder="Ingrese algo para buscar.."
                  />
                </a-form-item>
              </a-col>
              <template v-else>
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
              </template>
            </template>
            <a-col>
              <a-button type="primary" :disabled="isLoading" @click="fetchProgramming">
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
      </div>
    </div>

    <backend-table-component
      ref="tableRef"
      :items="programmings"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      @change="handleChange"
      :showHeader="false"
      :rowClassName="getRowClassName"
    >
      <template #file="{ record }">
        <p class="mb-0" style="font-size: 12px !important">
          <span class="d-block"
            ><b
              >{{ record.service.startDate }} | {{ record.service.equivalence }} |
              {{ record.service.name }}</b
            ></span
          >
          <span class="d-block"
            >{{ record.departure.startDate }} | {{ record.departure.name }} |
            {{ record.departure.type_iso }} | {{ record.departure.code }} |
            {{ record.departure.provider }}</span
          >
          <span class="d-block mb-0"
            ><b>FILE OPE: {{ record.departure.opeFile }}</b></span
          >
        </p>
      </template>
      <template #information="{ record }">
        <p class="text-center" style="font-size: 13px !important"><b>INFORMACIÓN</b></p>

        <p
          class="d-block text-center text-500 py-3 m-0"
          style="font-size: 13px !important"
          v-for="(file, index) in record.commercialFiles"
          :key="index"
        >
          <span class="d-block">{{ file.file.number }}</span>
          <span class="d-block"
            >{{ file.file.client }} | {{ file.file.language }} | {{ file.file.paxCount }} PAX</span
          >
        </p>
      </template>
      <template #hour="{ record }">
        <p class="text-center" style="font-size: 13px !important"><b>HORA</b></p>
        <p
          class="text-500 mb-0"
          style="font-size: 13px !important"
          v-for="file in record.commercialFiles"
          :key="file.id"
        >
          <span class="d-block py-4">{{ file?.assignment?.hour ?? '-' }}</span>
        </p>
      </template>
      <template #hotel="{ record }">
        <p class="text-center" style="font-size: 13px !important"><b>HOTEL</b></p>
        <p
          class="text-500 mb-0"
          style="font-size: 13px !important"
          v-for="file in record.commercialFiles"
          :key="file.id"
        >
          <span class="d-block py-4">{{ file?.assignment?.hotel ?? '-' }}</span>
        </p>
      </template>
      <template #train="{ record }">
        <p class="text-center" style="font-size: 13px !important"><b>TREN</b></p>
        <p
          class="text-500 mb-0"
          style="font-size: 13px !important"
          v-for="file in record.commercialFiles"
          :key="file.id"
        >
          <span class="d-block py-4">{{ file?.assignment?.train ?? '-' }}</span>
        </p>
      </template>
      <template #observations="{ record }">
        <p class="text-center" style="font-size: 13px !important"><b>OBSERVACIONES</b></p>
        <p
          class="text-500 mb-0"
          style="font-size: 13px !important"
          v-for="file in record.commercialFiles"
          :key="file.id"
        >
          <span class="d-block py-4">{{ file?.assignment?.obs ?? '-' }}</span>
        </p>
      </template>
      <template #provider="{ record }">
        <p
          class="text-500 mb-0"
          style="font-size: 13px !important"
          v-if="record.service.providers.length > 0"
        >
          <span class="d-block">
            <template v-if="!record.service.providers[0].isPermanent">
              <template v-if="record.service.providers[0].confirmationStatus === 'CONFIRMED'">
                <CheckOutlined />
              </template>
              <template v-if="record.service.providers[0].confirmationStatus === 'PENDING'">
                <ClockCircleOutlined />
              </template>
            </template>
            {{ record.service.providers?.[0]?.code ?? '-' }}
          </span>
        </p>
        <span v-else> - </span>
      </template>
      <template #actions="{ record }">
        <a-space>
          <a-dropdown>
            <template #overlay>
              <a-menu>
                <template v-if="getStatus(record) !== 'unprogrammed'">
                  <a-menu-item @click="handleProgram(record)">
                    <EditOutlined />
                    <b>Modificar</b> Programación</a-menu-item
                  >
                  <a-menu-item @click="handleResetProgram(record)">
                    <ReloadOutlined />
                    <b>Reiniciar</b> Programación</a-menu-item
                  >
                </template>
                <template v-else>
                  <a-menu-item @click="handleProgram(record)">
                    <ClockCircleOutlined /> Programar</a-menu-item
                  >
                  <a-menu-item @click="handleNoProgram(record)"
                    ><RetweetOutlined /> Convertir a no Programable</a-menu-item
                  >
                </template>
                <a-menu-item
                  :disabled="getStatus(record) !== 'programmed'"
                  @click="handleSendOrder(record)"
                >
                  <SendOutlined /> Enviar orden de servicio
                </a-menu-item>
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

  <a-modal
    v-model:visible="showModal"
    title="PROGRAMACIÓN DEL SERVICIO"
    :confirm-loading="isLoading || isLoadingDepartures"
    @ok="handleOkProvider"
    @cancel="handleCancel"
    okText="PROCESAR"
    cancelText="CANCELAR"
    width="800px"
  >
    <p class="text-center mb-0">
      <b>{{ programming.service.name ?? '' }} | {{ programming.service.startDate }}</b>
    </p>
    <p class="text-center mb-0">
      <span>{{ programming.departure.name }}</span>
    </p>
    <p class="text-center mb-0">
      <span
        >FILE OPE: <b>{{ programming.departure.opeFile }}</b></span
      >
    </p>

    <a-form layout="vertical">
      <a-form-item label="Buscar Proveedor:" name="provider">
        <a-input
          v-model:value="searchProvider"
          placeholder="Código o Nombre de proveedor"
          size="large"
          autocomplete="off"
          @change="handleSearchProviders"
        >
          <template #suffix>
            <LoadingOutlined v-if="isLoadingDepartures || isLoading" />
          </template>
        </a-input>
      </a-form-item>
    </a-form>

    <a-alert v-if="formProvider.provider" type="success" show-icon style="margin-top: 16px">
      <template #description>
        <div class="d-flex" style="gap: 7px">
          <a-typography-text type="success">Proveedor seleccionado:</a-typography-text>
          <a-typography-text type="success" strong>
            {{ formProvider.provider }}
          </a-typography-text>
        </div>
      </template>
    </a-alert>

    <a-alert
      v-if="programming?.service?.providers?.[0]?.code && !formProvider?.provider"
      type="success"
      show-icon
      style="margin-top: 16px"
    >
      <template #description>
        <div class="d-flex" style="gap: 7px">
          <a-typography-text type="success">Proveedor asignado inicialmente:</a-typography-text>
          <a-typography-text type="success" strong>
            {{ programming?.service?.providers?.[0]?.code }}
          </a-typography-text>
        </div>
      </template>
    </a-alert>

    <template
      v-if="guides.length > 0 && guides.filter((guide: any) => guide.flag_selected).length === 0"
    >
      <p class="mt-3 mb-0 text-500" style="font-size: 12px">SELECCIONAR:</p>

      <backend-table-component
        :items="guides"
        :columns="columnProviders"
        :options="tableOptionProviders"
        size="small"
      >
        <template #direcc="{ record }">
          <span style="font-size: 1rem">
            <CheckCircleOutlined v-if="record.numera === 'L'" />
            <CloseCircleOutlined v-else />
          </span>
        </template>
        <template #actions="{ index }">
          <a-tooltip placement="right" title="Seleccionar Proveedor">
            <a-button type="dashed" danger @click="handleAssignProvider(index)">
              <span style="font-size: 1.25rem; font-weight: bold; line-height: 1">
                <PlusOutlined />
              </span>
            </a-button>
          </a-tooltip>
        </template>
      </backend-table-component>
    </template>

    <div class="bg-light mt-3 p-2" style="border: 2px dashed #ddd">
      <a-row type="flex" justify="space-between" align="middle">
        <a-col :span="4">
          <b class="d-block text-center">INFO</b>
        </a-col>
        <a-col :span="3">
          <b class="d-block text-center">HORA</b>
        </a-col>
        <a-col :span="4">
          <b class="d-block text-center">HOTELES</b>
        </a-col>
        <a-col :span="4">
          <b class="d-block text-center">TRENES</b>
        </a-col>
        <a-col :span="7">
          <b class="d-block text-center">OBSERVACIONES</b>
        </a-col>
      </a-row>

      <template v-for="(file, f) in formProvider.files" :key="f">
        <a-row type="flex" justify="space-between" align="middle" class="my-2">
          <a-col :span="4">
            <small class="d-block text-center"
              ><b>{{ file.file.number }}</b></small
            >
            <small class="d-block text-center"
              >{{ file.file.client }} - {{ file.file.language }} - {{ file.paxs.length }} PAX{{
                file.paxs.length > 1 ? 'S' : ''
              }}</small
            >
          </a-col>
          <a-col :span="3">
            <a-input type="time" v-model:value="file.assignment.hour" />
          </a-col>
          <a-col :span="4">
            <a-input v-model:value="file.assignment.hotel" />
          </a-col>
          <a-col :span="4">
            <a-input v-model:value="file.assignment.train" />
          </a-col>
          <a-col :span="7">
            <a-input v-model:value="file.assignment.obs" />
          </a-col>
        </a-row>
      </template>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import { createVNode, onMounted, ref } from 'vue';
  import { useRoute } from 'vue-router';
  import { useDepartures, useProgramming } from '@/composables/adventure';
  import {
    CheckCircleOutlined,
    CheckOutlined,
    ClockCircleOutlined,
    CloseCircleOutlined,
    EditOutlined,
    ExclamationCircleOutlined,
    LoadingOutlined,
    PlusOutlined,
    ReloadOutlined,
    RetweetOutlined,
    SearchOutlined,
    SendOutlined,
    SettingFilled,
  } from '@ant-design/icons-vue';
  import BackendTableComponent from '../global/BackendTableComponent.vue';
  import { debounce } from 'lodash';
  import { Modal, notification } from 'ant-design-vue';

  const route = useRoute();
  const dateFormat = 'DD/MM/YYYY';

  const {
    isLoading: isLoadingDepartures,
    departure,
    findDeparture,
    searchGuides,
    guides,
  } = useDepartures();

  const getRowClassName = (record: any) => {
    return `align-top ${getStatus(record)}`;
  };

  const getStatus = (record: any) => {
    let status = 'with-order';
    if (
      !record?.programmingInformation?.status ||
      record?.programmingInformation?.status === 'UNPROGRAMMED'
    ) {
      status = 'unprogrammed';
    } else {
      status = record.programmingInformation.status === 'PROGRAMMED' ? 'programmed' : 'with-order';
    }
    return status;
  };

  const {
    isLoading,
    filters,
    fetchProgramming,
    states,
    types,
    pagination,
    programmings,
    programming,
    saveProgramming,
    updateProgramming,
    deactivateProgramming,
    resetProgramming,
    sendOrder,
    error,
  } = useProgramming();

  const searchProvider = ref('');

  const handleSearchProviders = debounce(async () => {
    guides.value = [];
    if (searchProvider.value.length > 2) {
      await searchGuides(searchProvider.value);
    }
  }, 500);

  const handleAssignProvider = (index: number) => {
    guides.value = guides.value.map((guide: any) => {
      return {
        ...guide,
        flag_selected: false,
      };
    });

    guides.value[index].flag_selected = !guides.value[index].flag_selected;
    formProvider.value.provider = guides.value[index].codigo ?? '-';
  };

  const columnProviders = [
    {
      title: 'Código',
      dataIndex: 'codigo',
      key: 'codigo',
      align: 'center',
    },
    {
      title: 'Razón',
      dataIndex: 'razon',
      key: 'razon',
      align: 'center',
    },
    {
      title: 'Tipo',
      dataIndex: 'type',
      key: 'type',
      align: 'center',
    },
    {
      title: 'Planta',
      dataIndex: 'direcc',
      key: 'direcc',
      align: 'center',
      isSlot: true,
    },
  ];

  const tableOptionProviders = {
    bordered: true,
    rowKey: '_id',
    pagination: false,
    showActions: true,
  };

  onMounted(async () => {
    const id = route.params.id;

    if (id) {
      await findDeparture(id);

      filters.value.type = 'FILE';
      filters.value.term = departure.value?.opeFile;
    }

    await fetchProgramming();
  });

  const clearFilters = () => {
    filters.value = {
      state: 'ALL',
      type: 'ALL',
      term: '',
      date_from: '',
      date_to: '',
    };
  };

  const columns = [
    {
      title: 'FILE',
      dataIndex: 'file',
      key: 'file',
      isSlot: true,
    },
    {
      title: 'Información',
      dataIndex: 'information',
      key: 'information',
      isSlot: true,
    },
    {
      title: 'Hora',
      dataIndex: 'hour',
      key: 'hour',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Hotel',
      dataIndex: 'hotel',
      key: 'hotel',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Tren',
      dataIndex: 'train',
      key: 'train',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Observaciones',
      dataIndex: 'observations',
      key: 'observations',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Proveedor',
      dataIndex: 'provider',
      key: 'provider',
      align: 'center',
      isSlot: true,
    },
  ];

  const tableOptions = {
    showActions: true,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  const tableRef = ref();

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;
    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);
    await fetchProgramming();
  };

  const showModal = ref(false);

  const handleProgram = (record: any) => {
    searchProvider.value = '';
    programming.value = record;
    formProvider.value.provider = '';
    guides.value = [];

    const files = record.commercialFiles.map((file: any) => {
      return {
        ...file,
        assignment: {
          hour: file.assignment?.hour,
          hotel: file.assignment?.hotel,
          train: file.assignment?.train,
          obs: file.assignment?.obs,
        },
      };
    });

    formProvider.value.files = JSON.parse(JSON.stringify(files));
    showModal.value = true;
  };

  const handleNoProgram = (programming: any) => {
    Modal.confirm({
      title: '¿Seguro de convertir este componente a NO PROGRAMABLE?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá reestablecer la información.',
      okText: 'Sí, continuar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await deactivateProgramming(programming._id);
        if (!error.value) {
          await fetchProgramming();
        }
      },
    });
  };

  const handleResetProgram = (programming: any) => {
    Modal.confirm({
      title: '¿Seguro de reiniciar la PROGRAMACIÓN del presente servicio?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá reestablecer la información.',
      okText: 'Sí, continuar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await resetProgramming(programming._id);
        if (!error.value) {
          await fetchProgramming();
        }
      },
    });
  };

  const handleSendOrder = (programming: any) => {
    Modal.confirm({
      title: '¿Seguro de enviar la orden de servicio?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá reestablecer la información.',
      okText: 'Sí, continuar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await sendOrder(programming._id);
        if (!error.value) {
          await fetchProgramming();
        }
      },
    });
  };

  const formProvider = ref({
    provider: '',
    files: [],
  });

  const handleOkProvider = async () => {
    const isUpdated = programming.value.programmingInformation?.status ?? 'UNPROGRAMMED';

    let providers = guides.value
      .filter((guide: any) => guide.flag_selected)
      .map((guide: any) => {
        return {
          code: guide.code,
          type: guide.type,
          isPermanent: guide.isPermanent,
        };
      });

    if (providers.length === 0) {
      providers = programming.value.service.providers.map((provider: any) => {
        return {
          code: provider.code,
          type: provider.type,
          isPermanent: provider.isPermanent,
        };
      });
    }

    if (providers.length === 0) {
      notification.error({
        message: 'Error',
        description: 'Debe seleccionar un proveedor.',
      });
      return;
    }

    const assignments = formProvider.value.files.map((file: any) => {
      return {
        ...file.assignment,
        commercialFile: file.file.number,
      };
    });

    const serviceId = programming.value._id;

    const params = {
      providers,
      assignments,
      serviceId: isUpdated === 'UNPROGRAMMED' ? serviceId : undefined,
    };

    try {
      if (isUpdated === 'UNPROGRAMMED') {
        await saveProgramming(params);
      } else {
        await updateProgramming(params);
      }

      if (!error.value) {
        await fetchProgramming();
      }
      showModal.value = false;
    } catch (err: any) {
      console.log('ERROR: ', err);
      notification.error({
        message: 'Error',
        description: err.data.message || 'Ocurrió un error al guardar el proveedor.',
      });
    }
  };

  const handleCancel = () => {
    showModal.value = false;
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
