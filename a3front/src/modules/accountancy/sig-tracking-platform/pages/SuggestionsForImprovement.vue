<template>
  <div class="suggestions-container">
    <!-- Vista de Listado -->
    <a-card v-if="typeView === 'L'" class="filters-card" :bordered="false">
      <!-- Filtros -->
      <div class="filter-section">
        <div class="filter-group">
          <span class="filter-label">Fechas:</span>
          <a-range-picker
            v-model:value="dateRange"
            format="DD-MM-YYYY"
            :placeholder="['Desde', 'Hasta']"
            style="width: 240px"
          />
        </div>

        <div class="filter-group">
          <a-button type="primary" @click="search" :loading="loading">
            <SearchOutlined />
            Buscar
          </a-button>
          <a-button @click="eraser" style="margin-left: 8px" :disabled="loading">
            <ClearOutlined />
            Limpiar
          </a-button>
        </div>

        <div class="filter-group">
          <a-button
            type="primary"
            danger
            @click="exportExcel"
            :disabled="items.length === 0 || loading"
          >
            <DownloadOutlined />
            Excel
          </a-button>
        </div>
      </div>
    </a-card>

    <a-card v-if="typeView === 'L'" style="margin-top: 16px">
      <template #title>
        <a-row>
          <a-col :span="12">
            <a-button type="primary" danger @click="changeView('F')"> Agregar Comentario </a-button>
          </a-col>
          <a-col :span="12">
            <a-input
              v-model:value="filterItems"
              placeholder="Búsqueda sobre el resultado..."
              style="width: 100%"
            >
              <template #prefix><SearchOutlined /></template>
            </a-input>
          </a-col>
        </a-row>
      </template>

      <a-table
        :columns="columns"
        :data-source="filteredItems"
        :loading="loading"
        :pagination="{ pageSize: 20 }"
        size="small"
        class="responsive-table"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'actions'">
            <a-dropdown :trigger="['click']">
              <a-button size="small">
                <SettingOutlined />
              </a-button>
              <template #overlay>
                <a-menu>
                  <a-menu-item @click="edit(record)"> <EyeOutlined /> Ver Detalle </a-menu-item>
                  <a-menu-item v-if="canDelete" danger @click="deleteItem(record.codref)">
                    <DeleteOutlined /> Eliminar
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>

          <template v-else-if="column.key === 'comentario'">
            <a-button
              type="link"
              size="small"
              @click="toggleContent(record, 'coment')"
              style="background: #f3f3f3"
            >
              <span v-if="record.coment" v-html="record.coment"></span>
              <span v-else>-</span>
            </a-button>
          </template>

          <template v-else-if="column.key === 'proveedor'">
            <div>{{ record.proveedor_servicio }} {{ record.vouch1 }} {{ record.vouch2 }}</div>
          </template>

          <template v-else-if="column.key === 'servicio'">
            <div>
              {{
                record.servicio && record.servicio !== '' ? record.servicio : 'OTROS PROVEEDORES'
              }}
            </div>
          </template>

          <template v-else-if="column.dataIndex && record[column.dataIndex]">
            <div class="multiline-cell">
              {{ record[column.dataIndex] }}
            </div>
          </template>

          <template v-else> - </template>
        </template>
      </a-table>
    </a-card>

    <!-- Vista de Formulario -->
    <a-row v-if="typeView === 'F'" :gutter="16">
      <a-col :span="16">
        <a-card title="Comentario de Mejora">
          <a-form :model="item" layout="vertical">
            <a-row :gutter="16">
              <!-- Nro File -->
              <a-col :span="12">
                <a-form-item label="Nro File" required>
                  <a-input
                    v-model:value="nrofile"
                    placeholder="Ingrese número de file"
                    @change="searchDetail"
                    :disabled="blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <!-- EC -->
              <a-col :span="12">
                <a-form-item label="EC">
                  <a-input v-model:value="item.codope" placeholder="EC" disabled />
                </a-form-item>
              </a-col>

              <!-- Nombre RSVA -->
              <a-col :span="12">
                <a-form-item label="Nombre RSVA">
                  <a-input v-model:value="item.descri" placeholder="Nombre RSVA" disabled />
                </a-form-item>
              </a-col>

              <!-- Pasajero -->
              <a-col :span="12" v-show="blocked === 0">
                <a-form-item label="Pasajero">
                  <a-select
                    v-model:value="selectedPassenger"
                    show-search
                    placeholder="Filtra por nombre"
                    :filter-option="false"
                    @search="filterPassengers"
                  >
                    <a-select-option v-for="p in allPassengers" :key="p.nrosec" :value="p.nrosec">
                      {{ p.nrodoc }} - {{ p.nombre }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="Pasajero">
                  <a-input v-model:value="item.descri" placeholder="Pasajero" disabled />
                </a-form-item>
              </a-col>

              <!-- Cliente -->
              <a-col :span="12">
                <a-form-item label="Cliente (Nombre)">
                  <a-input v-model:value="item.razon" placeholder="Cliente" disabled />
                </a-form-item>
              </a-col>

              <!-- Área -->
              <a-col :span="12">
                <a-form-item label="Área">
                  <a-input v-model:value="item.codsec" placeholder="Área" disabled />
                </a-form-item>
              </a-col>

              <!-- Tipo de Servicio -->
              <a-col :span="12">
                <a-form-item label="Tipo de Servicio">
                  <a-select
                    v-if="blocked === 0"
                    v-model:value="selectedTypeService"
                    show-search
                    placeholder="Filtra por descripción"
                    :filter-option="false"
                    @change="resetService"
                  >
                    <a-select-option v-for="t in allTypeServices" :key="t.codgru" :value="t.codgru">
                      {{ t.descri }}
                    </a-select-option>
                  </a-select>

                  <a-input v-else v-model:value="item.servicio" disabled />
                </a-form-item>
              </a-col>

              <!-- Servicio -->
              <a-col :span="12" v-show="blocked === 0">
                <a-form-item label="Servicio" required>
                  <a-select
                    v-if="blocked === 0"
                    v-model:value="selectedService"
                    show-search
                    placeholder="Filtra por código o descripción"
                    :filter-option="false"
                    @change="searchFechasDisponibles"
                    @search="handleServiceSearch"
                  >
                    <a-select-option v-for="s in allServices" :key="s.prefac" :value="s.prefac">
                      {{ s.prefac }} - {{ s.proveedor }}
                    </a-select-option>
                  </a-select>
                  <a-input v-else v-model:value="item.proveedor_servicio" disabled />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="Servicio">
                  <a-input :value="`${item.proveedor_servicio}`" disabled />
                </a-form-item>
              </a-col>

              <!-- Fecha del Servicio -->
              <a-col :span="12">
                <a-form-item label="Fecha del Servicio" required>
                  <a-select
                    v-if="fechasDisponibles.length > 0 && !manualDateMode"
                    v-model:value="fechaDisponible"
                    @click="showDateSelector"
                    @change="handleDateSelectChange"
                    format="DD-MM-YYYY"
                    placeholder="Seleccione fecha de la lista"
                    style="width: 100%"
                  >
                    <a-select-option v-for="t in fechasDisponibles" :key="t.fecin" :value="t.fecin">
                      {{ t.fecin }}
                    </a-select-option>

                    <a-select-option
                      disabled
                      key="separator"
                      class="ant-select-item-option-disabled"
                    >
                      ──────────────
                    </a-select-option>
                    <a-select-option value="MANUAL_ENTRY" style="font-weight: bold; color: #a10e19">
                      📅 Otra fecha (Calendario)
                    </a-select-option>
                  </a-select>

                  <div v-else style="display: flex; gap: 8px">
                    <a-date-picker
                      v-model:value="fechaDisponibleCalendario"
                      format="DD-MM-YYYY"
                      placeholder="Seleccione fecha"
                      style="width: 100%"
                      @change="handleDatePickerChange"
                    />

                    <a-tooltip title="Volver a la lista de fechas sugeridas">
                      <a-button v-if="fechasDisponibles.length > 0" @click="returnToDateList">
                        <template #icon><OrderedListOutlined /></template>
                      </a-button>
                    </a-tooltip>
                  </div>
                </a-form-item>
              </a-col>

              <a-col :span="24">
                <a-form-item label="Comentario" required>
                  <!-- Versión editable cuando blocked es 0 -->
                  <div v-if="blocked === 0">
                    <CloudinaryQuillEditor
                      ref="commentEditorRef"
                      v-model:modelValue="comment"
                      :placeholder="'Ingrese el comentario...'"
                      @image-upload-start="handleImageUploadStart"
                      @image-upload-complete="handleImageUploadComplete"
                    />

                    <!-- Indicador de upload -->
                    <div v-if="imageUploading" style="margin-top: 8px">
                      <a-alert message="Subiendo imágenes..." type="info" show-icon />
                    </div>
                  </div>

                  <!-- Versión de solo lectura cuando blocked es 1 -->
                  <div v-else>
                    <div v-if="comment" class="html-content-preview" v-html="comment"></div>
                    <div v-else class="html-content-empty">Sin comentario</div>
                  </div>
                </a-form-item>
              </a-col>

              <!-- Fecha Comentario -->
              <a-col :span="12">
                <a-form-item label="Fecha Comentario" required>
                  <a-date-picker
                    v-model:value="dateComment"
                    format="DD-MM-YYYY"
                    placeholder="Seleccione fecha"
                    style="width: 100%"
                  />
                </a-form-item>
              </a-col>
            </a-row>
          </a-form>
        </a-card>
      </a-col>

      <a-col :span="8">
        <a-card title="">
          <a-space direction="vertical" style="width: 100%">
            <a-button block @click="changeView('L')"> Ir al Listado </a-button>

            <a-button
              v-if="item.codref === 0"
              type="primary"
              danger
              block
              @click="save"
              :loading="loading"
            >
              Guardar
            </a-button>

            <a-button
              v-if="item.codref > 0"
              type="primary"
              danger
              block
              :loading="loading"
              @click="update"
            >
              Guardar
            </a-button>

            <a-button
              v-if="item.codref > 0 && canDelete"
              danger
              block
              :disabled="loading"
              @click="deleteItem(item.codref)"
            >
              Eliminar
            </a-button>
          </a-space>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted } from 'vue';
  import { debounce } from 'lodash-es';
  import { message, Modal } from 'ant-design-vue';
  import {
    SearchOutlined,
    ClearOutlined,
    DownloadOutlined,
    SettingOutlined,
    EyeOutlined,
    DeleteOutlined,
    OrderedListOutlined,
  } from '@ant-design/icons-vue';
  import moment from 'moment';
  import dayjs from 'dayjs';
  import {
    suggestionsForImprovementApi,
    nonConformingProductsApi,
    exportsApi,
  } from '../../services/api';
  import CloudinaryQuillEditor from '../../components/CloudinaryQuillEditor.vue';

  let user_ = ref('');

  const manualDateMode = ref(false);
  let searchTimeout = null;

  // QuillEditor
  const commentEditorRef = ref(null);
  // Estados para el upload de imágenes
  const imageUploading = ref(false);

  // Estado de la vista
  const typeView = ref('L');
  const loading = ref(false);
  const blocked = ref(0);
  const nrofile = ref('');
  const editable = ref(1);

  const showDatePicker = ref(false);

  // Filtros
  const dateRange = ref([dayjs().subtract(1, 'month'), dayjs()]);
  const filterItems = ref('');

  // Datos
  const items = ref([]);
  const allPassengers = ref([]);
  const allServices = ref([]);
  const allTypeServices = ref([]);
  const fechasDisponibles = ref([]);
  const fechaDisponible = ref('');
  const flagFechasDisponibles = ref(0);
  const fechaDisponibleCalendario = ref(null);
  const provider = ref({});

  // Selecciones
  const selectedPassenger = ref(null);
  const selectedService = ref(null);
  const selectedTypeService = ref(null);

  // Item actual
  const item = ref({
    codref: 0,
    nroref: '',
    codope: '',
    descri: '',
    razon: '',
    codsec: '',
    nombre: '',
    servicio: '',
    proveedor_servicio: '',
    vouch1: '',
    vouch2: '',
    _coment: '',
    itecom: 0,
  });

  // Permisos
  const canDelete = ref(false);

  const comment = ref('');
  const dateComment = ref('');

  // Columnas de la tabla
  const columns = [
    {
      title: 'N° SEG',
      dataIndex: 'nrocom',
      key: 'nrocom',
      width: 80,
      align: 'center',
    },
    {
      title: 'N° FILE',
      dataIndex: 'nroref',
      key: 'nroref',
      width: 80,
      align: 'center',
    },
    {
      title: 'NOMBRE DEL FILE',
      dataIndex: 'nombre_file',
      key: 'nombre_file',
      width: 150,
    },
    {
      title: 'USUARIO',
      dataIndex: 'codusu',
      key: 'codusu',
      width: 80,
      align: 'center',
    },
    {
      title: 'EJECUTIVA',
      dataIndex: 'codope',
      key: 'codope',
      width: 80,
      align: 'center',
    },
    {
      title: 'NOMBRE PAX',
      dataIndex: 'nombre',
      key: 'nombre',
      width: 120,
    },
    {
      title: 'ÁREA',
      dataIndex: 'area',
      key: 'area',
      width: 80,
      align: 'center',
    },
    {
      title: 'FECHA IN',
      dataIndex: 'diain',
      key: 'diain',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'FECHA OUT',
      dataIndex: 'diaout',
      key: 'diaout',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'SERVICIO',
      key: 'servicio',
      width: 120,
    },
    {
      title: 'PROVEEDOR',
      key: 'proveedor',
      width: 120,
    },
    {
      title: 'COMENTARIO',
      key: 'comentario',
      width: 150,
      ellipsis: false,
      align: 'center',
    },
    {
      title: 'FECHA SERVICIO',
      dataIndex: 'fecser',
      key: 'fecser',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'FECHA COMENTARIO',
      dataIndex: 'fecfel',
      key: 'fecfel',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: 80,
      align: 'center',
    },
  ];

  // Items filtrados
  const filteredItems = computed(() => {
    if (!filterItems.value) return items.value;

    const searchTerm = filterItems.value.toLowerCase();
    return items.value.filter((item) => {
      return Object.values(item).some((val) => String(val).toLowerCase().includes(searchTerm));
    });
  });

  // Métodos
  const formatDate = (dateString) => {
    if (!dateString) return '';

    if (dateString.includes('/')) {
      return dateString;
    }

    try {
      const date = new Date(dateString);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${day}/${month}/${year}`;
    } catch {
      return dateString;
    }
  };

  const formatDateForAPI = (dateValue) => {
    if (!dateValue) return '';

    if (dateValue && dateValue.format) {
      return dateValue.format('DD-MM-YYYY');
    }

    if (typeof dateValue === 'string' && dateValue.match(/^\d{2}-\d{2}-\d{4}$/)) {
      return dateValue;
    }

    return '';
  };

  const changeView = (view, fromEdit = false) => {
    typeView.value = view;
    if (view === 'F') {
      if (!fromEdit) {
        resetForm();
        blocked.value = 0;
      }
    } else if (view === 'L') {
      resetFormFields();
      setTimeout(() => search(), 100);
    }
  };

  const resetFormFields = () => {
    item.value = {
      codref: 0,
      nroref: '',
      codope: '',
      descri: '',
      razon: '',
      codsec: '',
      nombre: '',
      servicio: '',
      proveedor_servicio: '',
      vouch1: '',
      vouch2: '',
      _coment: '',
      itecom: 0,
    };

    nrofile.value = '';
    comment.value = '';

    selectedPassenger.value = '';
    selectedTypeService.value = '';
    selectedService.value = '';
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = dayjs();
    provider.value = {};
    showDatePicker.value = false;

    fechasDisponibles.value = [];
    allPassengers.value = [];
    allServices.value = [];

    blocked.value = 0;
  };

  const resetForm = () => {
    resetFormFields();
    filterItems.value = '';
    items.value = [];
  };

  const search = async () => {
    loading.value = true;

    try {
      const date1 = dateRange.value[0] ? formatDateForAPI(dateRange.value[0]) : '';
      const date2 = dateRange.value[1] ? formatDateForAPI(dateRange.value[1]) : '';

      if (date1 && date2) {
        const dayjsDate1 = dayjs(date1, 'DD-MM-YYYY');
        const dayjsDate2 = dayjs(date2, 'DD-MM-YYYY');

        if (dayjsDate1.isAfter(dayjsDate2)) {
          message.warning('Fechas incorrectas');
          loading.value = false;
          return;
        }
      }

      const response = await suggestionsForImprovementApi.get('/search', {
        params: {
          fecini: date1,
          fecfin: date2,
        },
      });

      items.value = response.data.data || [];
      loading.value = false;
    } catch (error) {
      console.error('Error en la búsqueda:', error);
      message.error(
        'Error al buscar comentarios: ' + (error.response?.data?.error || error.message)
      );
      loading.value = false;
    }
  };

  const eraser = () => {
    dateRange.value = [dayjs().subtract(1, 'month'), dayjs()];
    filterItems.value = '';
    items.value = [];
  };

  const searchDetail = debounce(async () => {
    if (!nrofile.value) return;

    try {
      const response = await nonConformingProductsApi.get(`/search-detail/${nrofile.value}`);

      const data = response.data.data || {};
      item.value.codsec = data.codsec || '';
      item.value.codope = data.codope || '';
      item.value.descri = data.descri || '';
      item.value.razon = data.razon || '';
      item.value.codcli = data.codcli || '';

      allPassengers.value = [];
      selectedPassenger.value = null;
      filterPassengers();
    } catch (error) {
      console.error('Error al buscar detalle:', error);
    }
  }, 350);

  const filterPassengers = async (searchText) => {
    if (!nrofile) return;

    try {
      const response = await suggestionsForImprovementApi.get('/search-passengers', {
        params: {
          nroref: nrofile.value,
          term: searchText,
        },
      });

      allPassengers.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar pasajeros:', error);
    }
  };

  const filterTypeServices = async () => {
    try {
      const response = await nonConformingProductsApi.get('/search-type-services', {
        params: {
          module: 'items',
          usuario: user_,
        },
      });

      allTypeServices.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar tipos de servicio:', error);
    }
  };

  const resetService = () => {
    allServices.value = [];
    selectedService.value = null;
    provider.value = {};
    showDatePicker.value = false;
    flagFechasDisponibles.value = 0;
    fechasDisponibles.value = [];
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = null;
    filterServices();

    if (selectedTypeService.value !== '0') {
      filterServices('');
    }
  };

  const showDateSelector = () => {
    flagFechasDisponibles.value = 1;
  };

  // Handlers para el estado de upload
  const handleImageUploadStart = () => {
    imageUploading.value = true;
  };

  const handleImageUploadComplete = () => {
    imageUploading.value = false;
  };

  const handleDatePickerChange = (date, dateString) => {
    fechaDisponibleCalendario.value = date;
    fechaDisponible.value = dateString;
  };

  const handleServiceSearch = (val) => {
    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }
    searchTimeout = setTimeout(() => {
      filterServices(val);
    }, 400);
  };
  const filterServices = async (searchText = '') => {
    // Si no hay file y no es otros proveedores, no buscamos
    if (!nrofile.value && selectedTypeService.value !== '0') return;

    try {
      const response = await nonConformingProductsApi.get('/search-services', {
        params: {
          nroref: nrofile.value,
          tipo_servicio: selectedTypeService.value,
          term: searchText, // El texto del input
          module: 'items',
        },
      });

      allServices.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar servicios:', error);
    }
  };

  const searchFechasDisponibles = async (searchText) => {
    if (!searchText || searchText.length < 2) return;

    fechaDisponible.value = '';
    manualDateMode.value = false; // Resetear modo manual

    let params_ = {
      nrofile: nrofile.value,
      prefac: editable.value ? searchText : selectedService.value,
    };

    try {
      const response = await nonConformingProductsApi.get('/search-fechas-disponibles', {
        params: params_,
      });

      fechasDisponibles.value = response.data.data || [];
      flagFechasDisponibles.value = fechasDisponibles.value.length > 0 ? 1 : 0;

      // Si no hay fechas, activamos modo manual
      if (fechasDisponibles.value.length === 0) {
        fechaDisponible.value = '';
        manualDateMode.value = true;
      }
    } catch (error) {
      message.error('Error al FechasDisponibles: ' + error.message);
      fechasDisponibles.value = [];
      flagFechasDisponibles.value = 0;
      manualDateMode.value = true;
    }
  };

  // 4. NUEVAS FUNCIONES DE CONTROL
  const handleDateSelectChange = (value) => {
    if (value === 'MANUAL_ENTRY') {
      manualDateMode.value = true;
      fechaDisponible.value = '';
      // fechaDisponibleCalendario.value = dayjs(); // Opcional
    } else {
      fechaDisponible.value = value;
    }
  };

  const returnToDateList = () => {
    manualDateMode.value = false;
    fechaDisponible.value = '';
  };

  const searchfechasDisponibles = async (searchText) => {
    if (!searchText || searchText.length < 2) return;

    fechaDisponible.value = '';

    let params_ = {
      nrofile: nrofile.value,
      prefac: editable.value ? searchText : selectedService.value,
    };
    try {
      const response = await nonConformingProductsApi.get('/search-fechas-disponibles', {
        params: params_,
      });

      fechasDisponibles.value = response.data.data || [];
      showDatePicker.value = true;
    } catch (error) {
      console.error('Error al buscar fechas disponibles:', error);
    }
  };

  const save = async () => {
    loading.value = true;

    try {
      // Verificar que todas las imágenes estén subidas
      const allUploaded = await checkAllImagesUploaded();
      if (!allUploaded) {
        message.warning('Espere a que terminen de subir todas las imágenes');
        loading.value = false;
        return;
      }

      // Limitar longitud del texto
      if (comment.value && comment.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }
      // Validaciones
      if (!nrofile.value) {
        message.error('Ingrese un file');
        loading.value = false;
        return;
      }

      if (!selectedService.value) {
        message.error('Seleccione un servicio');
        loading.value = false;
        return;
      }

      if (!comment.value) {
        message.error('Ingrese un comentario');
        loading.value = false;
        return;
      }

      const fechaDisponible_ =
        fechasDisponibles.value.length > 0
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaDisponible_) {
        message.error('Seleccione una fecha de Servicio');
        loading.value = false;
        return;
      }

      if (!dateComment.value) {
        message.error('Seleccione una fecha de Comentario');
        loading.value = false;
        return;
      }

      console.log(selectedPassenger);
      console.log(selectedPassenger.value);

      const data = {
        usuario: user_,
        nroref: nrofile.value,
        codcli: item.value.codcli,
        passenger: selectedPassenger.value,
        type_service: selectedTypeService.value,
        service: selectedService.value,
        provider: provider.value.codigo || '',
        comment: comment.value,
        date_service: fechaDisponible_,
        date: formatDateForAPI(dateComment.value),
      };

      const response = await suggestionsForImprovementApi.post('/save', data);

      if (response.data.data === 'success') {
        message.success('Comentario guardado correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al guardar el comentario');
      }
    } catch (error) {
      console.error('Error al guardar:', error);
      message.error('Error al guardar: ' + (error.response?.data?.error || error.message));
    } finally {
      loading.value = false;
    }
  };

  const edit = (record) => {
    console.log(record);
    resetFormFields();

    item.value = { ...record };
    nrofile.value = record.nroref;

    if (nrofile.value) {
      searchDetail();
    }

    comment.value = record.coment_alt || '';
    fechaDisponible.value = record.fecser;
    fechaDisponibleCalendario.value = record.fecser ? dayjs(record.fecser, 'DD-MM-YYYY') : dayjs();
    dateComment.value = record.fecfel ? dayjs(record.fecfel, 'DD-MM-YYYY') : dayjs();

    // Buscar fechas disponibles si hay proveedor
    if (record.prefac) {
      searchfechasDisponibles(record.prefac);
    }

    if (record.fecing) {
      if (record.fecing.includes('/')) {
        const parts = record.fecing.split('/');
        if (parts.length > 2) {
          fechaDisponible.value = `${parts[0]}-${parts[1]}-${parts[2]}`;
        } else {
          // Ya está en DD-MM-YYYY
          fechaDisponible.value = record.fecing;
        }
      } else {
        fechaDisponible.value = record.fecing;
      }
    } else {
      fechaDisponible.value = '';
    }

    if (record.fecing) {
      if (fechasDisponibles.value.length === 0) {
        // Solo establecer el datepicker si NO hay fechas disponibles de la API
        if (record.fecing.includes('/')) {
          const [day, month, year] = record.fecing.split('/');
          fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
        } else if (record.fecing.includes('-')) {
          const [day, month, year] = record.fecing.split('-');
          fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
        } else {
          fechaDisponibleCalendario.value = dayjs(record.fecing);
        }
      }
    } else {
      fechaDisponibleCalendario.value = null;
    }

    blocked.value = 1;
    changeView('F', true);
  };

  const update = async () => {
    loading.value = true;

    try {
      // Verificar que todas las imágenes estén subidas
      const allUploaded = await checkAllImagesUploaded();
      if (!allUploaded) {
        message.warning('Espere a que terminen de subir todas las imágenes');
        loading.value = false;
        return;
      }

      // Limitar longitud del texto
      if (comment.value && comment.value.length >= 30000) {
        message.error(
          'No está permitido la cantidad de texto ingresado en el comentario. Por favor, trate de acortar el contenido para continuar.'
        );
        loading.value = false;
        return;
      }

      // Validaciones (similares a save)
      if (!nrofile.value) {
        message.error('Ingrese un file');
        loading.value = false;
        return;
      }

      if (!comment.value) {
        message.error('Ingrese un comentario');
        loading.value = false;
        return;
      }

      const fechaDisponible_ =
        fechasDisponibles.value.length > 0
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaDisponible_) {
        message.error('Seleccione una fecha de Servicio');
        loading.value = false;
        return;
      }

      if (!dateComment.value) {
        message.error('Seleccione una fecha de comentario');
        loading.value = false;
        return;
      }

      const data = {
        usuario: user_,
        codref: item.value.codref,
        nroord: item.value.itecom,
        nroref: nrofile.value,
        passenger: selectedPassenger.value,
        type_service: selectedTypeService.value,
        service: selectedService.value,
        provider: provider.value.codigo || '',
        comment: comment.value,
        date_service: fechaDisponible_,
        date: formatDateForAPI(dateComment.value),
      };

      const response = await suggestionsForImprovementApi.put('/update', data);

      if (response.data.data === 'success') {
        message.success('Comentario actualizado correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al actualizar el comentario');
      }
    } catch (error) {
      console.error('Error al actualizar:', error);
      message.error('Error al actualizar: ' + (error.response?.data?.error || error.message));
    } finally {
      loading.value = false;
    }
  };

  const checkAllImagesUploaded = async () => {
    if (commentEditorRef.value) {
      const commentFiles = commentEditorRef.value.getAllFiles();
      if (commentFiles.total !== commentFiles.completed) return false;
    }
    return true;
  };

  const deleteItem = (codref) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar este comentario?',
      content: 'Esta acción no se puede deshacer',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Cancelar',
      onOk: async () => {
        try {
          await suggestionsForImprovementApi.delete(`/delete/${codref}`);
          message.success('Comentario eliminado correctamente');
          if (typeView.value === 'L') {
            search();
          } else {
            changeView('L');
          }
        } catch (error) {
          message.error('Error al eliminar: ' + error.message);
        }
      },
    });
  };

  const toggleContent = (record, type) => {
    if (type === 'coment' && record.coment !== record.coment_alt) {
      const aux = record.coment;
      record.coment = record.coment_alt;
      record.coment_alt = aux;
    }
  };

  const exportExcel = async () => {
    loading.value = true;

    try {
      const date1 = dateRange.value[0] ? formatDateForAPI(dateRange.value[0]) : '';
      const date2 = dateRange.value[1] ? formatDateForAPI(dateRange.value[1]) : '';

      if (date1 && date2) {
        const dayjsDate1 = dayjs(date1, 'DD-MM-YYYY');
        const dayjsDate2 = dayjs(date2, 'DD-MM-YYYY');
        if (dayjsDate1.isAfter(dayjsDate2)) {
          message.warning('Fechas incorrectas');
          loading.value = false;
          return;
        }
      }

      const params = {
        type: 'comments',
        fecin: dayjs(date1, 'DD-MM-YYYY').format('YYYY-MM-DD'),
        fecout: dayjs(date2, 'DD-MM-YYYY').format('YYYY-MM-DD'),
      };

      const response = await exportsApi.get('/excel', {
        params,
        responseType: 'blob',
      });

      downloadFile(response.data, `comentarios-mejora-${moment().format('YYYY-MM-DD')}.xlsx`);
      message.success('Excel descargado correctamente');
    } catch (error) {
      console.error('Error descargando Excel:', error);
      message.error('Error al descargar el Excel');
    } finally {
      loading.value = false;
    }
  };

  const downloadFile = (blobData, filename) => {
    const url = window.URL.createObjectURL(new Blob([blobData]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
  };

  // Cargar datos iniciales
  onMounted(async () => {
    try {
      user_ = localStorage.getItem('user_code');
      await search();

      // Verificar permisos de eliminación
      canDelete.value = true;

      // Cargar tipos de servicio
      await filterTypeServices('');
    } catch (error) {
      console.error('Error al cargar datos iniciales:', error);
    }
  });
</script>

<style scoped>
  .suggestions-container {
    padding: 16px;
  }

  .filters-card {
    margin-bottom: 16px;
    padding: 16px;
  }

  .filter-section {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 16px;
    flex-wrap: wrap;
  }

  .filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .filter-label {
    font-size: 13px;
    font-weight: 500;
    color: #595959;
    white-space: nowrap;
  }

  .responsive-table {
    font-size: 12px;
  }

  .responsive-table :deep(.ant-table-thead > tr > th) {
    //white-space: normal;
    //word-wrap: break-word;
    line-height: 1.2;
    padding: 8px 4px;
    font-size: 11px;
    font-weight: 600;
    background-color: #fafafa;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    //white-space: normal;
    //word-wrap: break-word;
    line-height: 1.3;
    padding: 6px 4px;
    vertical-align: top;
    font-size: 11px;
    height: auto !important;
    min-height: 40px;
    max-width: 450px;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td .ant-btn) {
    white-space: pre-wrap !important;
    word-break: break-word !important;
    height: auto !important;
    min-height: 24px;
    text-align: left;
    line-height: 1.4;
    padding: 4px 8px;
  }

  .responsive-table :deep(.ant-table) {
    table-layout: fixed;
  }

  .responsive-table :deep(.ant-table-tbody > tr) {
    height: auto !important;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    word-break: break-word;
    white-space: pre-wrap;
  }

  .multiline-cell {
    min-height: 32px;
    display: flex;
    align-items: center;
    word-wrap: break-word;
    word-break: break-word;
  }

  .comment-preview {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px solid #d9d9d9;
    min-height: 100px;
  }

  .date-picker-list {
    position: absolute;
    z-index: 1000;
    background: white;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    max-height: 200px;
    overflow-y: auto;
    width: 100%;
  }

  .date-item {
    padding: 8px 12px;
    cursor: pointer;
    border-bottom: 1px solid #f0f0f0;
  }

  .date-item:hover {
    background: #f5f5f5;
  }

  .date-item:last-child {
    border-bottom: none;
  }

  /* Responsive */
  @media (max-width: 768px) {
    .filter-section {
      flex-direction: column;
      align-items: flex-start;
      gap: 12px;
    }

    .filter-group {
      width: 100%;
    }

    .filter-group :deep(.ant-select) {
      flex: 1;
    }

    .filter-label {
      min-width: 100px;
      text-align: left;
    }
  }
</style>
