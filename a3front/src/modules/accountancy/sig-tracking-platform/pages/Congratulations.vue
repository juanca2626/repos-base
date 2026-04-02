<template>
  <div class="congratulations-container">
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
            :disabled="congratulations.length === 0 || loading"
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
            <a-button type="primary" danger @click="changeView('F')">
              Agregar Felicitación
            </a-button>
          </a-col>
          <a-col :span="12">
            <a-input
              v-model:value="filterCongratulations"
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
        :data-source="filteredCongratulations"
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
                  <a-menu-item v-if="canDelete" danger @click="deleteCongratulation(record.codref)">
                    <DeleteOutlined /> Eliminar
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>

          <template v-else-if="column.key === 'proveedor_servicio'">
            <div>
              {{ record.proveedor_servicio }} <br />
              <span v-html="record.vouch1"></span> <br />
              <span v-html="record.vouch2"></span>
            </div>
          </template>

          <template v-else-if="column.key === 'comentario'">
            <a-button
              type="link"
              size="small"
              @click="toggleContent(record)"
              style="background: #f3f3f3"
            >
              <span v-html="record.coment"></span>

              {{ record.coment_alt ? (record.coment_alt.length > 20 ? '...' : '') : '' }}
            </a-button>
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
        <a-card title="Felicitación">
          <a-form :model="congratulation" layout="vertical">
            <a-row :gutter="16">
              <a-col :span="12">
                <a-form-item label="NÚMERO DE FILE">
                  <a-input
                    v-model:value="nrofile"
                    :disabled="blocked === 1"
                    @change="searchDetail"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="EC">
                  <a-input
                    :value="congratulation.codope ? congratulation.codope.trim() : ''"
                    :disabled="congratulation.length > 0 || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="NOMBRE RSVA">
                  <a-input
                    :value="
                      congratulation.descri
                        ? congratulation.descri.trim()
                        : congratulation.nombre_file
                          ? congratulation.nombre_file.trim()
                          : ''
                    "
                    :disabled="congratulation.length > 0 || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 0">
                <a-form-item label="PASAJERO">
                  <a-select
                    v-model:value="selectedPassenger"
                    show-search
                    placeholder="Filtra por nombre"
                    :filter-option="false"
                    @search="filterPassengers"
                  >
                    <a-select-option v-for="p in allPassengers" :key="p.nrosec" :value="p.nrosec">
                      {{ p.nrodoc }} - {{ p.nombre.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="PASAJERO">
                  <a-input
                    :value="congratulation.nombre ? congratulation.nombre.trim() : ''"
                    disabled
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="CLIENTE (NOMBRE)">
                  <a-input
                    :value="congratulation.razon ? congratulation.razon.trim() : ''"
                    :disabled="congratulation.length > 0 || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="ÁREA">
                  <a-input
                    :value="
                      congratulation.codsec
                        ? congratulation.codsec.trim()
                        : congratulation.area
                          ? congratulation.area.trim()
                          : ''
                    "
                    :disabled="congratulation.length > 0 || blocked === 1"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 0">
                <a-form-item label="TIPO DE SERVICIO">
                  <a-select
                    v-model:value="selectedTypeService"
                    placeholder="Selecciona un tipo"
                    @change="resetService"
                  >
                    <a-select-option v-for="t in allTypeServices" :key="t.codgru" :value="t.codgru">
                      {{ t.descri.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 0">
                <a-form-item label="SERVICIO">
                  <a-select
                    v-model:value="selectedService"
                    show-search
                    placeholder="Filtra por código o descripción"
                    :filter-option="false"
                    @change="searchFechasDisponibles"
                    @search="handleServiceSearch"
                  >
                    <a-select-option v-for="s in allServices" :key="s.prefac" :value="s.prefac">
                      {{ s.prefac }} {{ s.vouch1 ? s.vouch1.toUpperCase() : '' }}
                      {{ s.vouch2 ? s.vouch2.toUpperCase() : '' }} - {{ s.proveedor.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="TIPO DE SERVICIO">
                  <a-input
                    :value="
                      congratulation.servicio && congratulation.servicio !== ''
                        ? congratulation.servicio.trim()
                        : 'OTROS PROVEEDORES'
                    "
                    disabled
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="SERVICIO">
                  <a-input
                    :value="`${congratulation.proveedor_servicio ? congratulation.proveedor_servicio.trim() : ''} ${congratulation.vouch1 ? congratulation.vouch1.trim() : ''} ${congratulation.vouch2 ? congratulation.vouch2.trim() : ''}`"
                    disabled
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="FECHA DEL SERVICIO">
                  <a-select
                    v-if="fechasDisponibles.length > 0 && !manualDateMode"
                    v-model:value="fechaDisponible"
                    @click="showDateSelector"
                    @change="handleDateSelectChange"
                    placeholder="Seleccione fecha disponible"
                    style="width: 100%"
                  >
                    <a-select-option
                      v-for="fecha in fechasDisponibles"
                      :key="fecha.fecin"
                      :value="fecha.fecin"
                    >
                      {{ fecha.fecin }}
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
                <a-form-item label="COMENTARIO">
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

              <a-col :span="12">
                <a-form-item label="FECHA FELICITACIÓN">
                  <a-date-picker
                    v-model:value="congratulationDate"
                    format="DD-MM-YYYY"
                    placeholder="Seleccione fecha"
                    style="width: 100%"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="allMotives.length > 0">
                <a-form-item label="MOTIVO">
                  <a-select
                    v-model:value="selectedMotive"
                    show-search
                    placeholder="Filtra por descripción"
                    :filter-option="false"
                    @search="filterMotives"
                  >
                    <a-select-option v-for="m in allMotives" :key="m.codgru" :value="m.codgru">
                      {{ m.descri.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1">
                <a-form-item label="MOTIVO">
                  <a-input
                    :value="
                      congratulation.detalle_motivo && congratulation.detalle_motivo !== ''
                        ? congratulation.detalle_motivo.trim()
                        : ''
                    "
                    disabled
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
              v-if="congratulation.codref === 0"
              type="primary"
              danger
              block
              @click="save"
              :loading="loading"
            >
              Guardar
            </a-button>

            <a-button
              v-if="congratulation.codref > 0"
              type="primary"
              danger
              block
              :loading="loading"
              @click="update"
            >
              Guardar
            </a-button>

            <a-button
              v-if="congratulation.codref > 0 && canDelete"
              danger
              block
              :disabled="loading"
              @click="deleteCongratulation(congratulation.codref)"
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
  import CloudinaryQuillEditor from '../../components/CloudinaryQuillEditor.vue';
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
  import { congratulationsApi, exportsApi, nonConformingProductsApi } from '../../services/api.js';

  const manualDateMode = ref(false);
  let searchTimeout = null;

  // QuillEditor
  const commentEditorRef = ref(null);
  // Estados para el upload de imágenes
  const imageUploading = ref(false);

  // Estado de la vista
  const typeView = ref('L'); // 'L' = Lista, 'F' = Formulario
  const loading = ref(false);

  let user_ = ref('');

  // Filtros
  const dateRange = ref([dayjs(), dayjs().add(1, 'day')]);
  // const dateRange = ref([dayjs().subtract(96, 'day'), dayjs().subtract(96, 'day')]);
  const filterCongratulations = ref('');

  // Datos
  const congratulations = ref([]);
  const allTypeServices = ref([]);
  const allPassengers = ref([]);
  const allServices = ref([]);
  const allMotives = ref([]);
  const fechasDisponibles = ref([]);
  const flagFechasDisponibles = ref(0);

  // Felicitación actual
  const congratulation = ref({
    codref: 0,
    nroref: '',
    codope: '',
    descri: '',
    nombre: '',
    razon: '',
    codsec: '',
    servicio: '',
    proveedor_servicio: '',
    detalle_motivo: '',
  });

  const nrofile = ref('');
  const blocked = ref(0);
  const editable = ref(1);

  // Selecciones
  const selectedPassenger = ref('');
  const selectedTypeService = ref('');
  const selectedService = ref('');
  const selectedMotive = ref('');
  const fechaDisponible = ref('');
  const fechaDisponibleCalendario = ref(dayjs());
  const congratulationDate = ref(dayjs());

  // Formulario
  const comment = ref('');

  // Permisos
  const canDelete = ref(false);

  const columns = [
    {
      title: 'N° SEG',
      dataIndex: 'nrocom',
      key: 'nrocom',
      width: 70,
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
      dataIndex: 'servicio',
      key: 'servicio',
      width: 120,
    },
    {
      title: 'PROVEEDOR',
      dataIndex: 'proveedor_servicio',
      key: 'proveedor_servicio',
      width: 150,
    },
    {
      title: 'COMENTARIO',
      key: 'comentario',
      width: 100,
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
      title: 'FECHA FELICITACIÓN',
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

  const checkAllImagesUploaded = async () => {
    if (commentEditorRef.value) {
      const commentFiles = commentEditorRef.value.getAllFiles();
      if (commentFiles.total !== commentFiles.completed) return false;
    }
    return true;
  };

  // Felicitaciones filtradas
  const filteredCongratulations = computed(() => {
    if (!filterCongratulations.value) return congratulations.value;

    const searchTerm = filterCongratulations.value.toLowerCase();
    return congratulations.value.filter((c) => {
      return Object.values(c).some((val) => String(val).toLowerCase().includes(searchTerm));
    });
  });

  // Métodos
  const formatDate = (dateString) => {
    if (!dateString) return '';

    if (dateString.includes('/')) {
      return dateString; // Ya está en formato DD/MM/YYYY
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

    // Si es un objeto DayJS
    if (dateValue && dateValue.format) {
      return dateValue.format('DD-MM-YYYY');
    }

    // Si es un string en formato DD-MM-YYYY
    if (typeof dateValue === 'string' && dateValue.match(/^\d{2}-\d{2}-\d{4}$/)) {
      return dateValue;
    }

    // Si es un Date object o ISO string
    if (dateValue instanceof Date || (typeof dateValue === 'string' && dateValue.includes('T'))) {
      const date = new Date(dateValue);
      const day = date.getDate().toString().padStart(2, '0');
      const month = (date.getMonth() + 1).toString().padStart(2, '0');
      const year = date.getFullYear();
      return `${day}-${month}-${year}`;
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
    congratulation.value = {
      codref: 0,
      nroref: '',
      codope: '',
      descri: '',
      nombre: '',
      razon: '',
      codsec: '',
      servicio: '',
      proveedor_servicio: '',
      detalle_motivo: '',
    };

    nrofile.value = '';

    selectedPassenger.value = '';
    selectedTypeService.value = '';
    selectedService.value = '';
    selectedMotive.value = '';
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = dayjs();
    congratulationDate.value = dayjs();

    comment.value = '';

    fechasDisponibles.value = [];
    allPassengers.value = [];
    allServices.value = [];
    allMotives.value = [];

    blocked.value = 0;
  };

  const resetForm = () => {
    resetFormFields();
    filterCongratulations.value = '';
    congratulations.value = [];
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

      const response = await congratulationsApi.get('/search', {
        params: {
          fecini: date1,
          fecfin: date2,
          nrocom: filterCongratulations.value,
        },
      });

      congratulations.value = response.data.data || [];
      loading.value = false;
    } catch (error) {
      console.error('Error en la búsqueda:', error);
      message.error(
        'Error al buscar felicitaciones: ' + (error.response?.data?.error || error.message)
      );
      loading.value = false;
    }
  };

  const eraser = () => {
    dateRange.value = [dayjs().subtract(1, 'month'), dayjs()];
    filterCongratulations.value = '';
    congratulations.value = [];
  };

  const searchDetail = debounce(async () => {
    if (!nrofile.value) return;

    blocked.value = 0;
    try {
      const response = await congratulationsApi.get('/search-detail', {
        params: { nroref: nrofile.value },
      });
      const data = response.data.data;
      congratulation.value.codsec = data.codsec ? data.codsec.trim() : '';
      congratulation.value.codope = data.codope ? data.codope.trim() : '';
      congratulation.value.descri = data.descri ? data.descri.trim() : '';
      congratulation.value.razon = data.razon ? data.razon.trim() : '';
      congratulation.value.codcli = data.codcli ? data.codcli.trim() : '';

      // allPassengers.value = [];
      filterPassengers();
    } catch (error) {
      message.error('Error al buscar File, no encontrado');
      console.log(error.message);
    }
  }, 350);

  const filterPassengers = async (searchText) => {
    try {
      const response = await nonConformingProductsApi.get('/search-passengers', {
        params: {
          nroref: nrofile.value,
          term: searchText,
        },
      });
      allPassengers.value = response.data.data;
    } catch (error) {
      console.error('Error al buscar pasajeros:', error);
    }
  };

  const handleServiceSearch = (val) => {
    // Si el tipo de servicio NO es "OTROS PROVEEDORES" (0), a veces no queremos filtrar por backend,
    // pero para mantener la consistencia del bloque 1, lo aplicamos general o con condición.
    // Aquí aplicamos la lógica de espera (debounce)

    if (searchTimeout) {
      clearTimeout(searchTimeout);
    }

    searchTimeout = setTimeout(() => {
      // Solo buscamos si hay texto o si queremos recargar la lista
      filterServices(val);
    }, 400); // Espera 400ms después de que el usuario deje de escribir
  };

  const filterServices = async (searchText = '') => {
    if (!nrofile.value && selectedTypeService.value !== '0') return;

    try {
      const response = await nonConformingProductsApi.get('/search-services', {
        params: {
          nroref: nrofile.value,
          term: searchText, // Aquí viaja lo que escribe el usuario
          tipo_servicio: selectedTypeService.value,
          module: 'congratulations',
        },
      });
      allServices.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar servicios:', error);
    }
  };

  const filterMotives = async (searchText) => {
    if (!selectedTypeService.value) return;

    try {
      const response = await congratulationsApi.get('/search-motives', {
        params: {
          type: selectedTypeService.value,
          term: searchText,
        },
      });
      allMotives.value = response.data.data;
    } catch (error) {
      console.error('Error al buscar motivos:', error);
    }
  };

  const resetService = () => {
    selectedService.value = '';
    allServices.value = [];
    flagFechasDisponibles.value = 0;
    fechasDisponibles.value = [];
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = null;
    filterServices();

    // Buscar motivos cuando se selecciona tipo de servicio
    if (selectedTypeService.value) {
      filterMotives('');
    }
    if (selectedTypeService.value !== '0') {
      filterServices('');
    }
  };

  const searchFechasDisponibles = async (searchText) => {
    if (!searchText || searchText.length < 2) return;

    fechaDisponible.value = '';
    manualDateMode.value = false; // Resetear modo manual al buscar

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

      // Si no hay fechas, forzar modo manual
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
      // Opcional: fechaDisponibleCalendario.value = dayjs();
    } else {
      fechaDisponible.value = value;
    }
  };

  const returnToDateList = () => {
    manualDateMode.value = false;
    fechaDisponible.value = '';
  };

  const handleDatePickerChange = (date, dateString) => {
    fechaDisponibleCalendario.value = date;
    fechaDisponible.value = dateString;
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

      const fechaServicio =
        fechasDisponibles.value.length > 0
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaServicio) {
        message.error('Seleccione una fecha de Servicio');
        loading.value = false;
        return;
      }

      if (!congratulationDate.value) {
        message.error('Seleccione una fecha de Felicitación');
        loading.value = false;
        return;
      }

      const data = {
        usuario: user_,
        nroref: nrofile.value,
        codcli: congratulation.value.codcli,
        passenger: selectedPassenger.value,
        type_service: selectedTypeService.value,
        service: selectedService.value,
        provider: '', // Ajustar según necesidad
        comment: comment.value,
        motive: selectedMotive.value,
        dateservice: fechaServicio,
        datecongratulation: formatDateForAPI(congratulationDate.value),
      };

      const response = await congratulationsApi.post('/save', data);

      if (response.data.data === 'success') {
        message.success('Felicitación guardada correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al guardar la felicitación');
      }
    } catch (error) {
      console.error('Error al guardar:', error);
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        message.error('Error: ' + error.message);
      } else {
        message.error('Error desconocido');
      }
    } finally {
      loading.value = false;
    }
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

      const fechaServicio =
        fechasDisponibles.value.length > 0
          ? fechaDisponible.value
          : formatDateForAPI(fechaDisponibleCalendario.value);

      if (!fechaServicio) {
        message.error('Seleccione una fecha de Servicio');
        loading.value = false;
        return;
      }

      if (!congratulationDate.value) {
        message.error('Seleccione una fecha de Felicitación');
        loading.value = false;
        return;
      }

      const data = {
        usuario: user_,
        codref: congratulation.value.codref,
        nroord: congratulation.value.itecom,
        nroref: nrofile.value,
        passenger: selectedPassenger.value,
        type_service: selectedTypeService.value,
        service: selectedService.value,
        provider: '',
        comment: comment.value,
        motive: selectedMotive.value,
        dateservice: fechaServicio,
        datecongratulation: formatDateForAPI(congratulationDate.value),
      };

      const response = await congratulationsApi.put('/update', data);

      if (response.data.data === 'success') {
        message.success('Felicitación actualizada correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al actualizar la felicitación');
      }
    } catch (error) {
      console.error('Error al actualizar:', error);
      if (error.response?.data?.error) {
        message.error(error.response.data.error);
      } else if (error.response?.data) {
        message.error(JSON.stringify(error.response.data));
      } else if (error.message) {
        message.error('Error: ' + error.message);
      } else {
        message.error('Error desconocido');
      }
    } finally {
      loading.value = false;
    }
  };

  const deleteCongratulation = (codref) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar esta felicitación?',
      content: 'Esta acción no se puede deshacer',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Cancelar',
      onOk: async () => {
        try {
          await congratulationsApi.delete(`/delete/${codref}`);
          message.success('Felicitación eliminada correctamente');
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

  const edit = (record) => {
    resetFormFields();

    congratulation.value = { ...record };
    nrofile.value = record.nroref;
    blocked.value = 1;

    comment.value = record.coment_alt || '';

    if (record.fecser) {
      if (record.fecser.includes('/')) {
        const parts = record.fecser.split('/');
        if (parts.length > 2) {
          fechaDisponible.value = `${parts[0]}-${parts[1]}-${parts[2]}`;
        } else {
          fechaDisponible.value = record.fecser;
        }
      } else {
        fechaDisponible.value = record.fecser;
      }
    }

    if (record.fecfel) {
      if (record.fecfel.includes('/')) {
        const [day, month, year] = record.fecfel.split('/');
        congratulationDate.value = dayjs(`${year}-${month}-${day}`);
      } else if (record.fecfel.includes('-')) {
        const [day, month, year] = record.fecfel.split('-');
        congratulationDate.value = dayjs(`${year}-${month}-${day}`);
      } else {
        congratulationDate.value = dayjs(record.fecfel);
      }
    }

    if (record.fecser && fechasDisponibles.value.length === 0) {
      if (record.fecser.includes('/')) {
        const [day, month, year] = record.fecser.split('/');
        fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
      } else if (record.fecser.includes('-')) {
        const [day, month, year] = record.fecser.split('-');
        fechaDisponibleCalendario.value = dayjs(`${year}-${month}-${day}`);
      } else {
        fechaDisponibleCalendario.value = dayjs(record.fecser);
      }
    }

    if (record.prefac) {
      searchFechasDisponibles(record.prefac);
    }

    changeView('F', true);
  };

  const toggleContent = (record) => {
    if (record.coment !== record.coment_alt) {
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
        type: 'congratulations',
        fecin: dayjs(date1, 'DD-MM-YYYY').format('YYYY-MM-DD'),
        fecout: dayjs(date2, 'DD-MM-YYYY').format('YYYY-MM-DD'),
      };

      const response = await exportsApi.get('/excel', {
        params,
        responseType: 'blob',
      });

      downloadFile(response.data, `felicitaciones-${moment().format('YYYY-MM-DD')}.xlsx`);
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

      const response = await nonConformingProductsApi.get('/search-type-services', {
        params: { module: 'congratulations', usuario: user_ },
      });
      allTypeServices.value = response.data.data || [];

      // Verificar permisos de eliminación
      canDelete.value = true; // Ajustar según lógica de permisos
    } catch (error) {
      console.error('Error al cargar datos iniciales:', error);
    }
  });
</script>

<style scoped>
  .congratulations-container {
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
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.2;
    padding: 8px 4px;
    font-size: 11px;
    font-weight: 600;
    background-color: #fafafa;
  }

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    padding: 6px 4px;
    vertical-align: top;
    font-size: 11px;
  }

  .multiline-cell {
    min-height: 32px;
    display: flex;
    align-items: center;
    word-wrap: break-word;
    word-break: break-word;
  }

  .html-content-preview {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px solid #d9d9d9;
    min-height: 40px;
    max-height: 300px;
    overflow-y: auto;
  }

  .html-content-empty {
    padding: 12px;
    background: #f5f5f5;
    border-radius: 4px;
    border: 1px dashed #d9d9d9;
    color: #999;
    text-align: center;
  }

  /* Estilos para imágenes en el preview */
  .html-content-preview :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 4px;
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
