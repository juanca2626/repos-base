<template>
  <div class="occurrences-container">
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
            :disabled="occurrences.length === 0 || loading"
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
              Agregar Seguimiento
            </a-button>
          </a-col>
          <a-col :span="12">
            <a-input
              v-model:value="filterOccurrences"
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
        :data-source="filteredOccurrences"
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
                  <a-menu-item v-if="canDelete" danger @click="deleteOccurrence(record.codref)">
                    <DeleteOutlined /> Eliminar
                  </a-menu-item>
                </a-menu>
              </template>
            </a-dropdown>
          </template>

          <template v-else-if="column.key === 'asignado_a'">
            <div>
              {{ record.proveedor_servicio !== 'SSOCUS' ? record.proveedor_servicio : 'SSOCUS' }}
            </div>
          </template>

          <template v-else-if="column.key === 'observaciones'">
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

          <template v-else-if="column.key === 'respuesta'">
            <a-button
              type="link"
              size="small"
              @click="toggleContent(record, 'respue')"
              style="background: #f3f3f3"
            >
              <span v-if="record.respue" v-html="record.respue"></span>
              <span v-else>-</span>
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
        <a-card title="Seguimiento">
          <a-form :model="occurrence" layout="vertical">
            <a-row :gutter="16">
              <a-col
                :span="12"
                v-show="
                  blocked === 0 ||
                  occurrence.proveedor_servicio === '' ||
                  occurrence.proveedor_servicio === null
                "
              >
                <a-form-item label="SEGUIMIENTO PARA EL USUARIO">
                  <a-select
                    v-model:value="selectedUser"
                    show-search
                    placeholder="Filtra por nombre o código"
                    :filter-option="false"
                    @search="filterUsers"
                    option-label-prop="label"
                    @change="onUserSelect"
                  >
                    <a-select-option
                      v-for="user in allUsers"
                      :label="`${user.codigo} - ${user.razon.toUpperCase()}`"
                      :key="user.codigo"
                      :value="user.codigo"
                    >
                      {{ user.codigo }} - {{ user.razon.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col
                :span="12"
                v-show="
                  blocked === 1 &&
                  occurrence.proveedor_servicio !== '' &&
                  occurrence.proveedor_servicio !== null
                "
              >
                <a-form-item label="SEGUIMIENTO PARA EL USUARIO">
                  <a-input
                    :value="`${occurrence.proveedor_servicio ? occurrence.proveedor_servicio.trim() : ''} ${occurrence.vouch1 ? occurrence.vouch1.trim() : ''} ${occurrence.vouch2 ? occurrence.vouch2.trim() : ''}`"
                    disabled
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12">
                <a-form-item label="FECHA DEL INCIDENTE">
                  <a-date-picker
                    v-model:value="fechaApertura"
                    format="DD-MM-YYYY"
                    placeholder="Seleccione fecha"
                    style="width: 100%"
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 0 || comentario === ''">
                <a-form-item label="COMENTARIO">
                  <a-select v-model:value="comentario">
                    <a-select-option value="1">No cumplió con el procedimiento</a-select-option>
                    <a-select-option value="2">No cumplió con plazo</a-select-option>
                    <a-select-option value="3"
                      >No uso canales de comunicación correctos</a-select-option
                    >
                    <a-select-option value="4"
                      >No delegó autoridades en caso de ausencia</a-select-option
                    >
                    <a-select-option value="5">Realizó mal sus funciones de puesto</a-select-option>
                    <a-select-option value="6">Entregó reportes fuera de fecha</a-select-option>
                    <a-select-option value="7"
                      >Generó incidencias por mala comunicacion</a-select-option
                    >
                    <a-select-option value="9"
                      >Cuenta exterior está contenta con EC</a-select-option
                    >
                    <a-select-option value="10"
                      >Pasajeros contentos con la Organización</a-select-option
                    >
                    <a-select-option value="11">Venta concretada vía Boletín Lito</a-select-option>
                    <a-select-option value="12"
                      >Superó récord mensual en tiempo de respuesta</a-select-option
                    >
                    <a-select-option value="8">Otros</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 0 || norma === ''">
                <a-form-item label="NORMA">
                  <a-select v-model:value="norma">
                    <a-select-option value="1">ISO</a-select-option>
                    <a-select-option value="2">SSO</a-select-option>
                    <a-select-option value="3">RSE</a-select-option>
                    <a-select-option value="4">GESTION EN EL TRABAJO</a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="12" v-show="blocked === 1 && comentario !== ''">
                <a-form-item label="COMENTARIO">
                  <a-input
                    :value="
                      occurrence.detalle_comentario ? occurrence.detalle_comentario.trim() : ''
                    "
                    disabled
                  />
                </a-form-item>

                <transition name="slide-fade">
                  <div v-if="simulationResult" id="simulation-card" class="simulation-result">
                    <div class="sim-header">
                      <span>Resultado de Simulación</span>
                      <a-button type="text" size="small" @click="simulationResult = null"
                        >✕</a-button
                      >
                    </div>

                    <div class="sim-body">
                      <div class="sim-section">
                        <strong>📧 Para (TO):</strong>
                        <div v-if="simulationResult.recipients.to.length">
                          <a-tag
                            color="blue"
                            v-for="email in simulationResult.recipients.to"
                            :key="email"
                          >
                            {{ email }}
                          </a-tag>
                        </div>
                        <span v-else class="text-muted">Ninguno</span>
                      </div>

                      <div
                        class="sim-section"
                        v-if="Object.keys(simulationResult.recipients.bcc || {}).length"
                      >
                        <strong>🕵️ Copia Oculta (BCC):</strong>
                        <div>
                          <a-tag
                            color="orange"
                            v-for="(email, key) in simulationResult.recipients.bcc"
                            :key="key"
                          >
                            {{ email }}
                          </a-tag>
                        </div>
                      </div>

                      <div class="sim-debug">
                        <small>Log: {{ simulationResult.debug_info?.log }}</small>
                      </div>
                    </div>
                  </div>
                </transition>
              </a-col>

              <a-col :span="12" v-show="blocked === 1 && norma !== ''">
                <a-form-item label="NORMA">
                  <a-input
                    :value="occurrence.detalle_norma ? occurrence.detalle_norma.trim() : ''"
                    disabled
                  />
                </a-form-item>
              </a-col>

              <a-col :span="12" v-if="allStatus.length > 0 && occurrence.codref > 0">
                <a-form-item label="ESTADO">
                  <a-select
                    v-model:value="selectedStatus"
                    show-search
                    placeholder="Filtra por tipo"
                    :filter-option="false"
                    @search="filterStatus"
                    @change="changeStatus"
                  >
                    <a-select-option
                      v-for="status in allStatus"
                      :key="status.codigo + '-' + status.tipniv"
                      :value="status.codigo + '-' + status.tipniv"
                    >
                      {{ status.desc.toUpperCase() }}
                    </a-select-option>
                  </a-select>
                </a-form-item>
              </a-col>

              <a-col :span="24">
                <a-form-item label="OBSERVACIONES">
                  <a-textarea
                    v-model:value="observaciones"
                    :rows="4"
                    placeholder="Ingrese las observaciones..."
                  />
                </a-form-item>
              </a-col>

              <a-col :span="24" v-show="viewResponse === 1 && occurrence.codref > 0">
                <a-form-item>
                  <a-textarea
                    v-model:value="respuesta"
                    :rows="5"
                    placeholder="Ingrese la respuesta..."
                  />
                </a-form-item>
              </a-col>

              <a-col
                :span="24"
                v-show="viewResponse === 0 && respuesta !== '' && occurrence.codref > 0"
              >
                <a-form-item label="RESPUESTA">
                  <div class="response-preview">
                    {{ respuesta }}
                  </div>
                </a-form-item>
              </a-col>

              <template v-if="specialUsers.includes(user_)">
                <a-col :span="8">
                  <a-form-item label="PENALIDAD ASUMIDA POR">
                    <a-select v-model:value="asumidoPor">
                      <a-select-option value="">----</a-select-option>
                      <a-select-option value="0001">RENTABILIDAD DEL FILE</a-select-option>
                      <a-select-option value="0002">ESPECIALISTA</a-select-option>
                      <a-select-option value="0003">OPE LIMA</a-select-option>
                      <a-select-option value="0004">OPE CUSCO</a-select-option>
                      <a-select-option value="0005">OPE AREQUIPA</a-select-option>
                      <a-select-option value="0006">OPE PUNO</a-select-option>
                      <a-select-option value="0007">PROVEEDOR</a-select-option>
                      <a-select-option value="0008">OTROS</a-select-option>
                      <a-select-option value="0009">GUIA PLANTA</a-select-option>
                      <a-select-option value="0010">GUIA FREELANCE</a-select-option>
                      <a-select-option value="0011">REPS</a-select-option>
                    </a-select>
                  </a-form-item>
                </a-col>

                <a-col :span="8">
                  <a-form-item label="MONTO">
                    <a-input-number
                      v-model:value="montoCompensacion"
                      style="width: 100%"
                      :min="0"
                      :precision="2"
                    />
                  </a-form-item>
                </a-col>

                <a-col :span="8">
                  <a-form-item label="OBS. COMPENSACIÓN">
                    <a-input
                      v-model:value="observacionesCompensacion"
                      placeholder="Observaciones..."
                    />
                  </a-form-item>
                </a-col>
              </template>
            </a-row>
          </a-form>
        </a-card>
      </a-col>

      <a-col :span="8">
        <a-card title="">
          <a-space direction="vertical" style="width: 100%">
            <transition name="fade">
              <a-button
                v-if="showSimulationBtn"
                type="dashed"
                block
                class="sim-btn"
                @click="saveSimulator"
                :loading="loading"
              >
                <template #icon><experiment-outlined /></template>
                Simular Correos
              </a-button>
            </transition>

            <a-button block @click="changeView('L')"> Ir al Listado </a-button>

            <a-button
              v-if="occurrence.codref === 0"
              type="primary"
              danger
              block
              @click="save"
              :loading="loading"
            >
              Guardar
            </a-button>

            <a-button
              v-if="occurrence.codref > 0"
              type="primary"
              danger
              block
              :loading="loading"
              @click="update"
            >
              Guardar
            </a-button>

            <a-button
              v-if="occurrence.codref > 0 && canDelete"
              danger
              block
              :disabled="loading"
              @click="deleteOccurrence(occurrence.codref)"
            >
              Eliminar
            </a-button>

            <a-checkbox
              v-if="occurrence.codref > 0 && specialUsers.includes(user_)"
              v-model:checked="flagNotify"
            >
              IGNORAR LAS NOTIFICACIONES POR CORREO
            </a-checkbox>

            <div v-if="related.length > 0" class="related-section">
              <p><b>FILE CON SEGUIMIENTOS RELACIONADOS:</b></p>
              <div v-for="item in related" :key="item.nrocom" class="related-item">
                N° {{ item.nrocom }} | Proveedor: {{ item.proveedor }}<br />
                Motivo: {{ item.descri_tipo }}<br />
                Compensación: {{ item.montot }}<br />
                Estado: {{ item.descri_estado }}<br /><br />
              </div>
            </div>
          </a-space>
        </a-card>
      </a-col>
    </a-row>
  </div>
</template>

<script setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import { message, Modal } from 'ant-design-vue';
  import {
    SearchOutlined,
    ClearOutlined,
    DownloadOutlined,
    SettingOutlined,
    EyeOutlined,
    DeleteOutlined,
  } from '@ant-design/icons-vue';
  import moment from 'moment';
  import dayjs from 'dayjs';
  import { managementMonitoringApi, usersApi, exportsApi } from '../../services/api';

  // Simulador
  const showSimulationBtn = ref(false);
  const simulationResult = ref(null);

  // Estado de la vista
  const typeView = ref('L');
  const loading = ref(false);
  let user_ = ref('');
  const blocked = ref(0);

  // Filtros
  const dateRange = ref([dayjs().subtract(1, 'month'), dayjs()]);
  const filterOccurrences = ref('');

  // Datos
  const occurrences = ref([]);
  const allUsers = ref([]);
  const allStatus = ref([]);

  // Ocurrencia actual
  const occurrence = ref({
    codref: 0,
    nrocom: '',
    proveedor_servicio: '',
    codusu: '',
    detalle_comentario: '',
    fecha_apertura: '',
    detalle_norma: '',
    fecha_cierre: '',
    coment: '',
    coment_alt: '',
    respue: '',
    respue_alt: '',
    descri_estado: '',
    itecom: 0,
    estado: '',
    tipniv: '',
    vouch1: '',
    vouch2: '',
    asupor: '',
    moncom: 0,
    obscom: '',
    nroref: '',
  });

  // Formulario
  const selectedUser = ref(null);
  const comentario = ref('');
  const norma = ref('');
  const observaciones = ref('');
  const respuesta = ref('');
  const fechaApertura = ref(dayjs());
  const selectedStatus = ref(null);
  const viewResponse = ref(0);

  watch(observaciones, (newVal) => {
    if (newVal && newVal.includes('!simular')) {
      showSimulationBtn.value = true;
    } else {
      showSimulationBtn.value = false;
      simulationResult.value = null; // Limpiar resultado si borran la palabra
    }
  });

  // Compensación
  const asumidoPor = ref('');
  const montoCompensacion = ref(0);
  const observacionesCompensacion = ref('');
  const flagNotify = ref(false);
  const related = ref([]);

  // Permisos
  const canDelete = ref(false);
  const specialUsers = ref(['LTM', 'CNS', 'LSC', 'DEV']);

  const columns = [
    {
      title: 'N° SEG',
      dataIndex: 'nrocom',
      key: 'nrocom',
      width: 70,
      align: 'center',
    },
    {
      title: 'ASIGNADO A',
      key: 'asignado_a',
      width: 120,
    },
    {
      title: 'USUARIO',
      dataIndex: 'codusu',
      key: 'codusu',
      width: 80,
      align: 'center',
    },
    {
      title: 'COMENTARIO',
      dataIndex: 'detalle_comentario',
      key: 'detalle_comentario',
      width: 150,
    },
    {
      title: 'FECHA APERTURA',
      dataIndex: 'fecha_apertura',
      key: 'fecha_apertura',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'TIPO DE SEG.',
      dataIndex: 'detalle_norma',
      key: 'detalle_norma',
      width: 120,
    },
    {
      title: 'FECHA CIERRE',
      dataIndex: 'fecha_cierre',
      key: 'fecha_cierre',
      width: 100,
      align: 'center',
      customRender: ({ text }) => (text ? formatDate(text) : ''),
    },
    {
      title: 'OBSERVACION',
      key: 'observaciones',
      width: 150,
      ellipsis: false,
      align: 'center',
    },
    {
      title: 'RESPUESTA',
      key: 'respuesta',
      width: 150,
      ellipsis: false,
      align: 'center',
    },
    {
      title: 'ESTADO',
      dataIndex: 'descri_estado',
      key: 'descri_estado',
      width: 100,
      align: 'center',
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: 80,
      align: 'center',
    },
  ];

  // Ocurrencias filtradas
  const filteredOccurrences = computed(() => {
    if (!filterOccurrences.value) return occurrences.value;

    const searchTerm = filterOccurrences.value.toLowerCase();
    return occurrences.value.filter((o) => {
      return Object.values(o).some((val) => String(val).toLowerCase().includes(searchTerm));
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
        occurrence.value.codref = 0;
      }
    } else if (view === 'L') {
      resetFormFields();
      setTimeout(() => search(), 100);
    }
  };

  const resetFormFields = () => {
    occurrence.value = {
      codref: 0,
      nrocom: '',
      proveedor_servicio: '',
      codusu: '',
      detalle_comentario: '',
      fecha_apertura: '',
      detalle_norma: '',
      fecha_cierre: '',
      coment: '',
      coment_alt: '',
      respue: '',
      respue_alt: '',
      descri_estado: '',
      itecom: 0,
      estado: '',
      tipniv: '',
      vouch1: '',
      vouch2: '',
      asupor: '',
      moncom: 0,
      obscom: '',
      nroref: '',
    };

    selectedUser.value = null;
    comentario.value = '';
    norma.value = '';
    observaciones.value = '';
    respuesta.value = '';
    fechaApertura.value = dayjs();
    selectedStatus.value = null;
    viewResponse.value = 0;
    asumidoPor.value = '';
    montoCompensacion.value = 0;
    observacionesCompensacion.value = '';
    flagNotify.value = false;
    related.value = [];

    blocked.value = 0;
  };

  const resetForm = () => {
    resetFormFields();
    filterOccurrences.value = '';
    occurrences.value = [];
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

      const response = await managementMonitoringApi.get('/search', {
        params: {
          fecini: date1,
          fecfin: date2,
          state: 'ALL',
        },
      });

      const responseData = response.data.data;

      if (responseData && responseData.results) {
        occurrences.value = responseData.results || [];

        if (responseData.status) {
          allStatus.value = responseData.status;
        }
      } else {
        occurrences.value = [];
      }

      loading.value = false;
    } catch (error) {
      console.error('Error en la búsqueda:', error);
      message.error(
        'Error al buscar seguimientos: ' + (error.response?.data?.error || error.message)
      );
      loading.value = false;
    }
  };

  const eraser = () => {
    dateRange.value = [dayjs().subtract(1, 'month'), dayjs()];
    filterOccurrences.value = '';
    occurrences.value = [];
  };

  const filterUsers = async (searchText) => {
    try {
      const response = await usersApi.get('/', {
        params: {
          term: searchText,
        },
      });
      allUsers.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar usuarios:', error);
    }
  };

  const onUserSelect = (user) => {
    selectedUser.value = user;
  };

  const filterStatus = async (searchText) => {
    try {
      const response = await managementMonitoringApi.get('/search-status', {
        params: {
          term: searchText,
        },
      });
      allStatus.value = response.data.data || [];
    } catch (error) {
      console.error('Error al buscar estados:', error);
    }
  };

  const changeStatus = () => {
    if (
      selectedStatus.value &&
      (selectedStatus.value.desc === 'CERRADO' || selectedStatus.value.desc === 'ELIMINADO')
    ) {
      viewResponse.value = 0;
    } else {
      if (selectedStatus.value && selectedStatus.value.desc === 'EN PROCESO') {
        // Lógica adicional si es necesario
      }
      viewResponse.value = 1;
    }
  };

  const save = async () => {
    loading.value = true;

    try {
      if (!selectedUser.value) {
        message.error('Seleccione un usuario');
        loading.value = false;
        return;
      }

      if (!comentario.value) {
        message.error('Ingrese un comentario');
        loading.value = false;
        return;
      }

      if (!fechaApertura.value) {
        message.error('Ingrese una fecha');
        loading.value = false;
        return;
      }

      if (!observaciones.value) {
        message.error('Ingrese una observación');
        loading.value = false;
        return;
      }

      const data = {
        usuario: user_,
        user: selectedUser.value,
        coment: comentario.value,
        norma: norma.value,
        date: formatDateForAPI(fechaApertura.value),
        observ: observaciones.value,
        respue: respuesta.value,
        asumido_por: asumidoPor.value,
        monto_compensacion: montoCompensacion.value,
        observaciones_compensacion: observacionesCompensacion.value,
      };

      const response = await managementMonitoringApi.post('/save', data);

      if (response.data.data === 'success') {
        message.success('Seguimiento guardado correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al guardar el seguimiento');
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

  const saveSimulator = async () => {
    loading.value = true;
    simulationResult.value = null;

    try {
      // Validaciones mínimas para que el simulador funcione
      if (!selectedUser.value) {
        message.error('Seleccione un usuario para simular');
        loading.value = false;
        return;
      }

      // Preparar data (Idéntico a save/update)
      const data = {
        usuario: user_, // Usuario conectado (LTM, etc)
        // Mapeo exacto para el Controller saveTest
        user: selectedUser.value.codigo || selectedUser.value, // El código del proveedor
        coment: comentario.value,
        norma: norma.value,
        date: formatDateForAPI(fechaApertura.value),
        observ: observaciones.value,
        asumido_por: asumidoPor.value,
        monto_compensacion: montoCompensacion.value,
        observaciones_compensacion: observacionesCompensacion.value,

        // Datos extra por si es update (para simular con datos existentes)
        codref: occurrence.value.codref || 0,
        estado: selectedStatus.value ? selectedStatus.value.codigo : 'P',
      };

      const response = await managementMonitoringApi.post('/save-simulator', data);

      if (response.data.success) {
        message.success('Simulación calculada exitosamente');
        simulationResult.value = response.data.data;

        // Scroll hacia el resultado
        setTimeout(() => {
          document.getElementById('simulation-card')?.scrollIntoView({ behavior: 'smooth' });
        }, 100);
      } else {
        message.error('Error en la respuesta del simulador');
      }
    } catch (error) {
      console.error('Error en simulación:', error);
      message.error('Error al simular: ' + (error.response?.data?.error || error.message));
    } finally {
      loading.value = false;
    }
  };

  const update = async () => {
    loading.value = true;

    try {
      if (!observaciones.value) {
        message.error('Ingrese las observaciones');
        loading.value = false;
        return;
      }

      let flagNotifyValue = 0;

      if (
        !asumidoPor.value ||
        !montoCompensacion.value ||
        parseFloat(montoCompensacion.value) === 0
      ) {
        if (!respuesta.value) {
          message.error('Ingrese una respuesta');
          loading.value = false;
          return;
        }
        flagNotifyValue = 1;
      }

      let user_selected_ = selectedUser.value ? selectedUser.value.codigo : occurrence.value.codusu;
      let status_selected_ =
        selectedStatus.value && selectedStatus.value.codigo
          ? selectedStatus.value.codigo
          : occurrence.value.codusu;

      if (!status_selected_) {
        message.error('Ingrese el estado');
        loading.value = false;
        return;
      }

      const data = {
        usuario: user_,
        codref: occurrence.value.codref,
        nroord: occurrence.value.itecom,
        user: user_selected_ ? user_selected_ : selectedUser.value,
        coment: comentario.value,
        norma: norma.value,
        date: formatDateForAPI(fechaApertura.value),
        flag_notify: flagNotify.value ? 1 : flagNotifyValue,
        observ: observaciones.value,
        respue: respuesta.value,
        estado: status_selected_,
        asumido_por: asumidoPor.value,
        monto_compensacion: montoCompensacion.value,
        observaciones_compensacion: observacionesCompensacion.value,
      };

      const response = await managementMonitoringApi.put('/update', data);

      if (response.data.data === 'success') {
        message.success('Seguimiento actualizado correctamente');
        changeView('L');
      } else {
        message.error(response.data.data || 'Error al actualizar el seguimiento');
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

  const deleteOccurrence = (codref) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar este seguimiento?',
      content: 'Esta acción no se puede deshacer',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Cancelar',
      onOk: async () => {
        try {
          await managementMonitoringApi.delete(`/delete/${codref}`);
          message.success('Seguimiento eliminado correctamente');
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

  const edit = async (record) => {
    resetFormFields();

    occurrence.value = { ...record };
    blocked.value = 1;

    observaciones.value = record._coment || '';
    respuesta.value = record.respue_alt || '';

    if (record.fecha_apertura) {
      if (record.fecha_apertura.includes('/')) {
        const [day, month, year] = record.fecha_apertura.split('/');
        fechaApertura.value = dayjs(`${year}-${month}-${day}`);
      } else if (record.fecha_apertura.includes('-')) {
        const [day, month, year] = record.fecha_apertura.split('-');
        fechaApertura.value = dayjs(`${year}-${month}-${day}`);
      } else {
        fechaApertura.value = dayjs(record.fecha_apertura);
      }
    }

    const currentStatus = allStatus.value.find(
      (status) => status.codigo + '-' + status.tipniv === record.estado + '-' + record.tipniv
    );

    if (currentStatus) {
      selectedStatus.value = currentStatus;
      occurrence.value.descri_estado = currentStatus.desc;
      changeStatus();
    }

    asumidoPor.value = record.asupor || '';
    montoCompensacion.value = parseFloat(record.moncom) || 0;
    observacionesCompensacion.value = record.obscom || '';

    // Cargar seguimientos relacionados
    await searchRelated();

    changeView('F', true);
  };

  const searchRelated = async () => {
    try {
      const response = await managementMonitoringApi.get('/search-related', {
        params: {
          nrofile: occurrence.value.nroref,
          nrocom: occurrence.value.nrocom,
        },
      });

      const responseData = response.data.data;
      related.value = responseData?.results || [];
    } catch (error) {
      console.error('Error al buscar relacionados:', error);
    }
  };

  const toggleContent = (record, type) => {
    if (type === 'coment' && record.coment !== record.coment_alt) {
      const aux = record.coment;
      record.coment = record.coment_alt;
      record.coment_alt = aux;
    } else if (type === 'respue' && record.respue !== record.respue_alt) {
      const aux = record.respue;
      record.respue = record.respue_alt;
      record.respue_alt = aux;
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
        type: 'occurrences',
        state: 'ALL',
        fecin: dayjs(date1, 'DD-MM-YYYY').format('YYYY-MM-DD'),
        fecout: dayjs(date2, 'DD-MM-YYYY').format('YYYY-MM-DD'),
      };

      const response = await exportsApi.get('/excel', {
        params,
        responseType: 'blob',
      });

      downloadFile(response.data, `seguimientos-${moment().format('YYYY-MM-DD')}.xlsx`);
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
      // user_.value = localStorage.getItem('user_code');  ??
      user_ = localStorage.getItem('user_code');
      await search();

      // Verificar permisos de eliminación
      canDelete.value = true;
    } catch (error) {
      console.error('Error al cargar datos iniciales:', error);
    }
  });
</script>

<style scoped>
  .occurrences-container {
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

  .responsive-table :deep(.ant-table-tbody > tr > td) {
    white-space: normal;
    word-wrap: break-word;
    line-height: 1.3;
    padding: 6px 4px;
    vertical-align: top;
    font-size: 11px;
    height: auto !important;
    min-height: 40px;
    max-width: 250px;
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

  .related-section {
    margin-top: 16px;
    padding: 12px;
    background: #f9f9f9;
    border-radius: 4px;
    border: 1px solid #e8e8e8;
  }

  .related-item {
    margin-bottom: 8px;
    padding-bottom: 8px;
    border-bottom: 1px solid #e8e8e8;
  }

  .related-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
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
