<template>
  <div class="base-table container-fluid p-0">
    <a-alert type="error" showIcon class="mb-4" v-show="showAlertMessage">
      <template #icon><smile-outlined /></template>
      <template #message
        ><div class="text-danger">{{ alertMessage }}</div></template
      >
    </a-alert>

    <div class="row g-0 row-filter">
      <a-form
        ref="formFilterRef"
        class="form-filter"
        :model="formFilter"
        autocomplete="off"
        @finish="onFilter"
        @finishFailed="onFilterFailed"
        style="margin-bottom: 0.3cm"
      >
        <a-range-picker
          style="height: 45px; margin-left: 1cm"
          name="dateRange"
          v-model:value="formFilter.dateRange"
          valueFormat="DD-MM-YYYY"
        />
        <div>
          <a-button
            class="base-button"
            width="60"
            size="large"
            type="primary"
            danger
            ghost
            @click="handleSearch"
          >
            <font-awesome-icon icon="fa-solid fa-search" />
          </a-button>
        </div>
      </a-form>
    </div>

    <!--    Para Buscar con placeholder-->
    <div class="search-bar">
      <div class="search-container">
        <a-space wrap :size="14">
          <a-typography-text strong>Buscar:</a-typography-text>
          <a-input
            name="filter"
            size="large"
            width="210"
            placeholder="Filtra"
            autocomplete="off"
            :allow-clear="true"
            v-model:value="formFilter.filter"
          />
        </a-space>

        <div class="status-group">
          <div class="status-indicator">
            <div class="square green"></div>
            <a-typography-text>Pagado</a-typography-text>
          </div>

          <div class="status-indicator">
            <div class="square red"></div>
            <a-typography-text style="color: #1a171d">Pendiente de pago</a-typography-text>
          </div>
        </div>
      </div>
    </div>
    <!---->

    <div class="base-table container-fluid p-0">
      <!-- ENCABEZADO DE LA TABLA -->
      <div class="row g-0 row-header" :style="{ backgroundColor: '#bc090f', color: 'black' }">
        <div class="col-small">N°</div>
        <!-- Checkbox en el encabezado -->
        <!--<div class="col-small"></div>-->
        <div class="col-checkbox">
          <a-checkbox v-model:checked="allSelected" @change="toggleAllRows" />
        </div>
        <div class="col-small" v-for="column in config.columns.slice(2)" :key="column.key">
          <span class="row-header-sort">{{ column.title }}</span>
        </div>
      </div>
      <!-- CUERPO DE LA TABLA -->
      <div v-if="isLoading" class="container-body is-loading">
        <a-spin tip="Cargando statements..." />
      </div>
      <div v-else-if="data?.length === 0" class="container-body is-loading">
        <a-empty>
          <template #description> <span>Vacío</span></template></a-empty
        >
      </div>
      <div v-else class="container-body">
        <div class="row g-0 row-body" v-for="(file, key) in data" :key="file.id">
          <!--para dibujar el checkbox al lado de N -->
          <div class="col-small">{{ (currentPageValue - 1) * currentPageSize + key + 1 }}</div>
          <div class="col-checkbox">
            <a-checkbox v-model:checked="selectedRows[file.id]" @change="toggleRow(file.id)" />
          </div>
          <!---->
          <div class="col-small col-small-200 text-uppercase">{{ file.file }}</div>
          <div
            class="col-small col-small-break col-small-200 text-uppercase"
            v-html="file.groupName"
          ></div>
          <div class="col-small text-uppercase">{{ file.paxCount }}</div>
          <div class="col-small col-small-200 text-uppercase">{{ file.entryDate }}</div>
          <div class="col-small text-uppercase">{{ file.exitDate }}</div>
          <div class="col-small text-uppercase">{{ file.paymentDeadline }}</div>
          <div class="col-small">{{ file.amount }}</div>
        </div>
      </div>
    </div>

    <div class="row g-0 row-pagination">
      <a-pagination
        v-model:current="currentPageValue"
        v-model:pageSize="currentPageSize"
        :disabled="data?.length === 0"
        :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
        :total="total"
        show-size-changer
        show-quick-jumper-off
        :scroll="{ x: 'max-content' }"
        @change="onChange"
      >
        <template #buildOptionText="props">
          <span>{{ props.value }}</span>
        </template>
      </a-pagination>
    </div>

    <a-space
      direction="vertical"
      style="
        width: 100%;
        display: flex;
        justify-content: center;
        align-items: center;
        margin-top: 10px;
      "
    >
      <a-space>
        <a-button type="primary" @click="maxTagCount++">INVOICES</a-button>
        <a-button type="primary" @click="maxTagCount--">OUTSTANDING ACCOUNT</a-button>
      </a-space>
    </a-space>
  </div>
</template>

<script setup>
  // import { formatDate } from '@/utils/files.js';
  // import { reactive, ref, computed, watch } from 'vue'; // Se comentó por huski
  import { useStatementsStore } from '@/modules/statements/stores/statements-store';
  import { ref, reactive, onMounted, watch } from 'vue';
  import { useCustomersStore } from '@/stores/global/customers-store.js'; // Importar store de clientes
  import Cookies from 'js-cookie';
  // import { useRouter } from 'vue-router';
  // import BaseBadge from './BaseBadge.vue';
  // import BasePopover from './BasePopover.vue';
  // import BaseSelect from './BaseSelect.vue';
  // import PopoverHoverAndClick from './PopoverHoverAndClick.vue';
  // import FilesPopoverVip from '@/components/files/edit/FilesPopoverVip.vue';
  // import {} from //   useStatusesStore,
  //   useHaveInvoicesStore,
  //   useRevisionStagesStore,
  //   useExecutivesStore,
  //   useClientsStore,
  //   useItineraryStore,
  //   useDownloadStore,
  //   useFilesStore,
  ('@store/files');
  import { notification } from 'ant-design-vue';
  // import { debounce } from 'lodash-es';
  // import { useI18n } from 'vue-i18n';
  // const { t } = useI18n({
  //   useScope: 'global',
  // });
  // const router = useRouter();
  // const statusesStore = useStatusesStore();
  // const haveInvoicesStore = useHaveInvoicesStore();
  // const revisionStagesStore = useRevisionStagesStore();
  // const executivesStore = useExecutivesStore();
  // const clientsStore = useClientsStore();
  // const itineraryStore = useItineraryStore();
  // const downloadStore = useDownloadStore();
  // const filesStore = useFilesStore();
  // const statusByIso = (iso) => statusesStore.getStatusByIso(iso);
  // const haveInvoiceByIso = (iso) => haveInvoicesStore.getHaveInvoiceByIso(iso);
  // const getRevisionStageById = (id) => revisionStagesStore.getRevisionStageById(id);
  // const DEFAULT_FILTER_BY = null;
  // const selectedFilter = ref(DEFAULT_FILTER_BY);
  const maxTagCount = ref(0);

  const allSelected = ref(false);
  const selectedRows = ref({});

  // Función para seleccionar o deseleccionar todas las filas
  const toggleAllRows = () => {
    selectedRows.value = allSelected.value;
    selectedRows.value[file.id] = allSelected.value;
  };

  // Función para seleccionar o deseleccionar una fila individualmente
  const toggleRow = (id) => {
    selectedRows.value[id] = !selectedRows.value[id];
    allSelected.value = Object.values(selectedRows.value).every(Boolean);
  };

  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 10;
  const DEFAULT_PAGE_SIZE_OPTIONS = [10, 20];
  const DEFAULT_FORM_FILTER = {
    filter: '',
    clientId: null,
    dateRange: [],
  };
  const formFilter = reactive(DEFAULT_FORM_FILTER);
  const formFilterRef = ref();
  const statementsStore = useStatementsStore();
  const customersStore = useCustomersStore();
  const selectedCustomer = ref(null); // Cliente seleccionado

  const props = defineProps({
    config: { type: Object, default: () => ({}) },
    total: { type: Number, default: 0 },
    isLoading: { type: Boolean, default: false },
    data: { type: Array, default: () => [] },
    currentPage: { type: Number, default: 0 },
    defaultPerPage: { type: Number, default: 0 },
    perPage: { type: Number, default: 10 },
    searchOption: { type: String, default: 'op1' },
  });

  onMounted(() => {
    const storedClientId = Cookies.get('client_id_limatour');
    const storedClientCode = Cookies.get('client_code_limatour');
    const storedClientName = Cookies.get('client_name_limatour');
    //const storedCustomer = localStorage.getItem('selectedCustomer');
    if (storedClientId && storedClientCode && storedClientName) {
      selectedCustomer.value = {
        id: storedClientId,
        code: storedClientCode,
        name: storedClientName,
      };
      customersStore.setSelectedCustomer(selectedCustomer.value);
    }
  });

  const showAlertMessage = ref(false);
  const alertMessage = ref('');

  const handleSearch = () => {
    const storedClientId = Cookies.get('client_id_limatour');
    const storedClientCode = Cookies.get('client_code_limatour');

    if (!storedClientId) {
      console.error('ERROR: No hay cliente seleccionado.');
      showAlertMessage.value = true;
      alertMessage.value = 'Debes elegir un cliente para continuar';
      return;
    } else {
      showAlertMessage.value = false;
      alertMessage.value = '';
    }
    // Enviar la búsqueda al store
    console.log('formulario: ', formFilter);
    let data_ = {
      currentPage: 1,
      perPage: props.perPage,
      filter: formFilter.filter.trim(), // Mantiene el texto ingresado en el input
      clientCode: storedClientCode, // Cliente seleccionado
      dateFrom: formFilter.dateRange.length ? formFilter.dateRange[0] : null, // Fechas seleccionadas
      dateTo: formFilter.dateRange.length ? formFilter.dateRange[1] : null,
      searchOption: props.searchOption,
    };

    console.log(data_.dateFrom);
    if (data_.dateFrom == null) {
      showAlertMessage.value = true;
      alertMessage.value = 'Debes elegir fechas para continuar';
      return;
    } else {
      showAlertMessage.value = false;
      alertMessage.value = '';
    }
    /*
    _date = _date.split('-');
      _date = _date[2] + '/' + _date[1] + '/' + _date[0];
    * */
    console.log(data_);

    statementsStore.fetchAll(data_);

    console.log('Parámetros enviados:', {
      filter: formFilter.filter.trim(),
      searchOption: props.searchOption,
    });
  };

  const emit = defineEmits(['onChange', 'onFilter', 'onRefresh']);
  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);

  watch(
    () => props.data,
    (newData) => {
      console.log('Datos recibidos en la tabla:', newData);
    },
    { deep: true }
  );

  //@typescript-eslint/no-unused-vars
  const onFilterFailed = (errorInfo) => {
    console.log('Failed:', errorInfo);
    notification['error']({
      message: `Filter`,
      description: errorInfo,
      duration: 8,
    });
  };

  const onChange = (page, perSize) => {
    console.log('Página seleccionada:', page, 'Tamaño de página:', perSize);
    const storedClientCode = Cookies.get('client_code_limatour');
    emit('onChange', {
      currentPage: page,
      perPage: perSize,
      filter: formFilter.filter.trim(), // Mantiene el texto ingresado en el input
      clientCode: storedClientCode, // Cliente seleccionado
      dateFrom: formFilter.dateRange.length ? formFilter.dateRange[0] : null, // Fechas seleccionadas
      dateTo: formFilter.dateRange.length ? formFilter.dateRange[1] : null,
      searchOption: props.searchOption,
    });
  };
  // const isDesc = computed(() => selectedFilter?.value?.filterByType === 'desc');
  // const isSelectedFilterBy = (fieldName) => selectedFilter?.value?.filterBy === fieldName;
  // const onFilterBy = ({ filterBy, filterByType }) => {
  //   selectedFilter.value = DEFAULT_FILTER_BY;
  //   selectedFilter.value = { filterBy, filterByType };
  //   emit('onFilterBy', selectedFilter.value);
  // };
  // const handlePopoverClickQuotation = ({ itineraryId }) => {
  //   itineraryStore.getById({ itineraryId });
  // };
  // const handleSearchExecutives = debounce((value) => {
  //   if (value != '' || (value == '' && executivesStore.getExecutives.length == 0)) {
  //     executivesStore.fetchAll(value);
  //   }
  // }, 300);
  // const handleSearchClients = debounce((value) => {
  //   if (value != '' || (value == '' && clientsStore.getClients.length == 0)) { clientsStore.fetchAll(value);
  //   }
  // }, 300);
  // const handleGoToEdit = (id) => {
  //   if (!id) return;
  //   router.push({ name: 'files-edit', params: { id } });
  // };
  // const handleRefresh = () => {
  //   console.log('q tal');
  //   filesStore.revisionStages = null;
  //   filesStore.filterNextDays = null;
  //   filesStore.opeAssignStages = null;
  //   formFilterRef.value.resetFields();
  //   currentPageValue.value = 1;
  // };
  // const handleRefreshCache = () => {
  //   emit('handleRefreshCache');
  // };
</script>

<style scoped lang="scss">
  .base-table {
    .row {
      display: flex;
      align-items: center;
      border-radius: 0px; /* Aquí el borde redondeado si le doy número y cero para ser recto*/
      margin-bottom: 15px;
      text-align: center;
      font-size: 0.875rem;
    }
    .row-header {
      background-color: var(--files-background-1);
      color: var(--files-black-4);
      min-height: 50px;
      font-weight: 700;
      border-top: none; /* para agregar línea en el encabezado de la tabla*/
    }
    .row-header-sort {
      cursor: pointer;
    }
    .container-body {
      min-height: 370px;
      // min-height: 465px;
      &.is-loading {
        background: #fafafa;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    }
    .row-body {
      background-color: var(--files-gray-1);
      border: 1px solid var(--files-main-colorgray-1);
      min-height: 40px;
      font-weight: 400;
      border-bottom: 1px solid black; /* para agregar línea en cada fila de la tabla*/
    }
    .col-small {
      flex: 1 0 0%;
      &-break {
        word-break: break-all;
        text-align: center;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      &-200 {
        width: 200px;
      }
    }
    .col-checkbox {
      width: 50px; /* Ajusta el ancho del checkbox */
      display: flex;
      justify-content: center;
      align-items: center;
    }
    .col-body-options {
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--files-main-color);
      gap: 8px;
      svg {
        cursor: pointer;
        padding: 1px;
        width: 16px;
        height: 16px;
      }
      svg:focus {
        outline: none;
      }
    }
  }
  .box-title {
    font-weight: 500;
    font-size: 12px;
    line-height: 18px;
    color: #2f353a;
  }
  .box-description {
    font-weight: 400;
    font-size: 12px;
    line-height: 18px;
  }
  .form-filter {
    display: flex;
    gap: 1rem;
  }
  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 1.625rem;
  }
  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    color: #c4c4c4;
    .is-vip {
      color: var(--files-exclusives);
    }
    &:hover {
      color: var(--files-exclusives);
    }
  }
  .row-pagination {
    display: flex;
    justify-content: center;
    gap: 60px;
    padding-top: 20px;
  }
  .text-uppercase {
    text-transform: uppercase;
  }
  .vip-content-click {
    font-family: var(--files-font-basic);
    width: 410px;
    height: 260px;
    // height: 337px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    &-title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 23px;
      padding: 20px 12px 23px;
      text-align: center;
      letter-spacing: -0.015em;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;
      margin-bottom: 1.8125rem;
    }
  }
  .quotation-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    &-title {
      font-weight: 900;
      font-size: 1.2rem;
      line-height: 1.2;
    }
    &-subtitle {
      font-weight: 400;
      font-size: 0.8rem;
      span {
        text-decoration: underline;
        font-weight: 500;
      }
    }
    &-buttons {
      display: flex;
      gap: 5px;
      padding-bottom: 10px;
    }
    &-table {
      width: 310px;
      box-shadow:
        0 3px 6px -2px rgb(0 0 0 / 20%),
        0 6px 12px rgb(0 0 0 / 10%);
      margin-top: 5px;
      & :deep(.ant-table-cell) {
        font-size: 0.7rem;
      }
    }
  }
  .details-content-click {
    font-family: var(--files-font-basic);
    width: 275px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-title {
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
    }
    &-subtitle {
      font-weight: 600;
    }
    &-description {
      font-weight: 400;
    }
  }
  .pending-tasks-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 202px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-body {
      overflow-y: auto;
    }
    &-footer {
      height: 100px;
      overflow-y: auto;
      display: flex;
      justify-content: end;
    }
    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }
    &-subtitle {
      font-weight: 600;
    }
    &-description {
      font-weight: 400;
      font-size: 0.75rem;
      line-height: 1.1875;
    }
  }
  .actions-content-click {
    font-family: var(--files-font-basic);
    width: 185px;
    height: auto;
    display: flex;
    flex-direction: column;
    padding: 3px;
    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }
    &-content {
      display: flex;
      flex-direction: column;
    }
  }
  .status-row {
    &-error {
      width: 7px;
      height: 7px;
      background: #c21d3b;
      box-shadow: 0px 0px 0px 2px rgba(194, 29, 59, 0.24);
      border-radius: 100px;
    }
    &-success {
      width: 7px;
      height: 7px;
      background: #1ed790;
      box-shadow: 0px 0px 0px 2px rgba(30, 215, 144, 0.25);
      border-radius: 100px;
    }
    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }
    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 3px #e3d7f2;
      border-radius: 100px;
    }
  }
  .is-col-hidden {
    display: none;
  }
  .col-small {
    min-width: 93px;
  }
  .search-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 92%;
    margin: 0 auto;
    gap: 8px; /* Espacio entre "Buscar" y el input */
  }
  .status-group {
    display: flex;
    align-items: center;
    gap: 22px; /* Aumenta la separación entre "Pagado" y "Pendiente de pago" */
  }
  .status-indicator {
    display: flex;
    align-items: center;
  }
  .square {
    width: 25px; /* Aumenta el ancho del cuadrado de pagado y pendiente de pago */
    height: 12px;
    border-radius: 2px;
    margin-right: 5px;
    border: 1px solid black; /* Agrega un borde negro */
  }
  .square.green {
    background-color: #b2e8a8;
  }
  .square.red {
    background-color: #ebb3b3;
  }
  .search-bar {
    margin-bottom: 1cm; /* Espacio entre "Buscar" y la tabla */
  }
</style>
