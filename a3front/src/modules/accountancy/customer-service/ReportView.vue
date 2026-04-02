<template>
  <div class="classic-workspace-container">
    <!-- List View -->
    <div v-if="typeView === 'L'" class="fade-in">
      <div class="classic-top-bar">
        <div class="top-left">
          <a-button class="btn-classic-new" @click="openCreation">
            <template #icon><PlusOutlined /></template>
            Agregar Reporte
          </a-button>
          <div class="classic-search-input-wrapper">
            <a-input
              v-model:value="searchText"
              placeholder="Búsqueda sobre el resultado ..."
              class="classic-search-input"
              @pressEnter="handleSearch"
            >
              <template #prefix><SearchOutlined style="color: #bfbfbf" /></template>
            </a-input>
          </div>
        </div>

        <div class="toolbar-container">
          <a-space wrap align="center">
            <a-radio-group v-model:value="filterStatus">
              <a-radio-button value="PE">Pendientes</a-radio-button>
              <a-radio-button value="CE">Cerrados</a-radio-button>
              <a-radio-button value="ALL">Todos</a-radio-button>
            </a-radio-group>

            <a-divider type="vertical" />

            <a-input-group compact style="display: flex">
              <a-select v-model:value="filterSearchType" style="width: 140px">
                <a-select-option value="FECSEG">F. Recepción</a-select-option>
                <a-select-option value="DATEIN">F. Inicio</a-select-option>
              </a-select>
              <a-date-picker
                v-model:value="startDate"
                format="DD/MM/YYYY"
                placeholder="Desde"
                style="width: 120px"
              />
              <a-date-picker
                v-model:value="endDate"
                format="DD/MM/YYYY"
                placeholder="Hasta"
                style="width: 120px"
              />
            </a-input-group>

            <a-button type="primary" @click="handleSearch" :loading="loading">
              <template #icon><SearchOutlined /></template> Buscar
            </a-button>

            <a-button @click="exportExcel">
              <template #icon><CloudDownloadOutlined style="color: #52c41a" /></template>
            </a-button>
          </a-space>
        </div>
      </div>

      <!-- Classic Table View -->
      <div class="classic-table-wrapper">
        <a-table
          :columns="columns"
          :data-source="dataSource"
          :pagination="{ pageSize: 20 }"
          :scroll="{ x: 1500 }"
          :loading="loading"
          size="small"
          class="modernized-classic-table"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'actions'">
              <a-tooltip title="Ver Detalles">
                <a-button
                  class="p-1"
                  type="ghost"
                  @click="openDetailsModal(record)"
                  :class="getCommentButtonClass(record)"
                >
                  <CommentOutlined />
                </a-button>
              </a-tooltip>
              <a-tooltip title="Editar">
                <a-button type="ghost" class="p-1" @click="openEdit(record)">
                  <EditOutlined />
                </a-button>
              </a-tooltip>
              <a-button type="ghost" class="p-1" danger @click="handleDeleteReport(record)">
                <DeleteOutlined />
              </a-button>
            </template>
            <template v-else-if="column.key === 'status'">
              <span
                :class="[
                  'status-dot-indicator',
                  record.status?.toLowerCase().replace(' ', '-') || 'pendiente',
                ]"
              >
                {{ record.status }}
              </span>
            </template>
          </template>
          <template #emptyText>
            <div class="empty-text-classic">
              <InboxOutlined style="font-size: 24px; color: #d9d9d9; margin-bottom: 8px" />
              <p>No se encontraron reportes para mostrar</p>
            </div>
          </template>
        </a-table>
      </div>
    </div>

    <!-- Form View -->
    <div v-else class="classic-form-container fade-in">
      <div class="form-header-premium">
        <div class="form-title-area">
          <h2 class="form-main-title">
            {{ isEditing ? 'Editar Reporte #' + formData.codref : 'Nuevo Reporte' }}
          </h2>
          <p class="form-subtitle">Seguimiento y resolución de incidencias operativas</p>
        </div>
      </div>

      <div class="classic-form-body-grid">
        <!-- Main Form Area (Scrollable) -->
        <div class="form-main-scrollable">
          <a-form layout="vertical" class="premium-classic-form">
            <div class="form-section-card">
              <a-row :gutter="32">
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>NÚMERO DE FILE</label>
                    <a-input
                      v-model:value="formData.nroref"
                      placeholder="Ej: 405534"
                      @blur="handleSearchDetail"
                    />
                  </div>
                  <div class="classic-form-item">
                    <label>NOMBRE RESERVA</label>
                    <a-input
                      v-model:value="formData.reservationName"
                      placeholder="Nombre de la reserva"
                    />
                  </div>
                  <div class="classic-form-item">
                    <label>CLIENTE</label>
                    <a-input v-model:value="formData.clientName" placeholder="Nombre del cliente" />
                  </div>
                  <div class="classic-form-item">
                    <label>ÁREA</label>
                    <a-input v-model:value="formData.area" placeholder="Ej: C3O1" />
                  </div>
                </a-col>
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>ESPECIALISTA</label>
                    <a-input
                      v-model:value="formData.codusu"
                      placeholder="Código del especialista"
                    />
                  </div>
                  <!-- div class="classic-form-item">
                    <label>JEFE</label>
                    <a-input v-model:value="formData.userep" placeholder="Código del jefe" />
                  </div -->
                </a-col>
              </a-row>
              <a-row :gutter="32">
                <a-col :span="24">
                  <div class="classic-form-item">
                    <label>RESUMEN CASO</label>
                    <a-textarea
                      v-model:value="formData.resume"
                      placeholder="Breve descripción del caso"
                      :auto-size="{ minRows: 2 }"
                    />
                  </div>
                </a-col>
              </a-row>
              <div class="classic-form-item">
                <label class="classic-quill-label">QUIEN REPORTÓ EL CASO</label>
                <a-row :gutter="32" class="classic-form-item">
                  <a-col :span="24">
                    <a-radio-group
                      v-model:value="formData.quien_reporto"
                      class="radio-selection-group"
                    >
                      <a-radio value="RO1" class="custom-radio-lt">COMERCIAL</a-radio>
                      <a-radio value="RO2" class="custom-radio-lt">OPERACIONES</a-radio>
                      <a-radio value="RO3" class="custom-radio-lt">RPT. POR TRABAJAR</a-radio>
                      <a-radio value="RO4" class="custom-radio-lt">OTROS</a-radio>
                    </a-radio-group>
                  </a-col>
                </a-row>
              </div>

              <!-- Conditional OTRO field -->
              <div class="classic-form-item" v-if="formData.quien_reporto === 'RO4'">
                <label>OTRO</label>
                <a-input v-model:value="formData.otruse" placeholder="Especifique otro..." />
              </div>
              <div class="classic-form-item">
                <label>COMENTARIO</label>
                <div v-if="!hasInitialComment" class="quill-premium-wrapper">
                  <CloudinaryQuillEditor v-model="formData.comment" :style="{ height: '180px' }" />
                </div>
                <div v-else class="readonly-content-box" v-html="formData.comment"></div>
              </div>
              <div class="classic-form-item">
                <label>TIPO DE RESPUESTA</label>
                <a-radio-group
                  v-model:value="formData.tipo_respuesta"
                  class="radio-selection-group"
                >
                  <a-radio value="RO1" class="custom-radio-lt">R. INFORMATIVO</a-radio>
                  <a-radio value="RO2" class="custom-radio-lt">R. INCIDENTE</a-radio>
                  <a-radio value="RO3" class="custom-radio-lt">R. NO SHOW</a-radio>
                  <a-radio value="RO4" class="custom-radio-lt">R. ENFERMEDAD / DOLENCIA</a-radio>
                </a-radio-group>
              </div>
              <div class="classic-form-item">
                <label>RESPUESTA</label>
                <div v-if="!hasInitialResponse" class="quill-premium-wrapper">
                  <CloudinaryQuillEditor v-model="formData.response" :style="{ height: '180px' }" />
                </div>
                <div v-else class="readonly-content-box" v-html="formData.response"></div>
              </div>
              <div class="classic-form-item">
                <a-checkbox v-model:checked="formData.isClosed">REPORTE CERRADO</a-checkbox>
              </div>
              <a-row :gutter="32">
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>FECHA DE LLEGADA</label>
                    <a-date-picker
                      v-model:value="formData.datein"
                      format="DD/MM/YYYY"
                      class="w-full"
                    />
                  </div>
                </a-col>
                <a-col :span="12" v-if="formData.response || formData.isClosed">
                  <div class="classic-form-item">
                    <label>FECHA DE CIERRE</label>
                    <a-date-picker
                      v-model:value="formData.dateout"
                      format="DD/MM/YYYY"
                      class="w-full"
                    />
                  </div>
                </a-col>
              </a-row>
            </div>
          </a-form>
        </div>

        <!-- Sidebar Panel (Control Board) -->
        <div class="form-sidebar-fixed">
          <div class="claims-control-board">
            <div class="board-section">
              <a-button class="btn-board gray" @click="typeView = 'L'">
                <template #icon><ArrowLeftOutlined /></template>
                Ir al Listado
              </a-button>
            </div>

            <div class="board-section">
              <div class="board-separator">ACCIONES DE GESTIÓN</div>
              <a-button
                class="btn-board red highlighted"
                @click="handleSave"
                :loading="saving"
                :disabled="searchingDetail || (formData.nroref && !fileDetailFound)"
              >
                <template #icon><SaveOutlined /></template>
                Guardar Reporte
              </a-button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Details Modal -->
    <a-modal
      v-model:open="detailsModalOpen"
      title="Detalles del Reporte"
      width="900px"
      :footer="null"
    >
      <div class="details-modal-content">
        <div class="detail-section">
          <h4 class="detail-section-title"><CommentOutlined /> COMENTARIO</h4>
          <div class="detail-content" v-html="selectedReport?.comment || 'Sin comentario'"></div>
        </div>

        <a-divider />

        <div class="detail-section">
          <h4 class="detail-section-title"><CheckCircleOutlined /> RESPUESTA</h4>
          <div class="detail-content" v-html="selectedReport?.respue || 'Sin respuesta'"></div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, reactive, onMounted, createVNode } from 'vue';
  import {
    SearchOutlined,
    CloudDownloadOutlined,
    PlusOutlined,
    InboxOutlined,
    ArrowLeftOutlined,
    SaveOutlined,
    DeleteOutlined,
    EditOutlined,
    CommentOutlined,
    CheckCircleOutlined,
    ExclamationCircleOutlined,
  } from '@ant-design/icons-vue';
  import { notification, Modal } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { debounce } from 'lodash-es';
  import customParseFormat from 'dayjs/plugin/customParseFormat';
  dayjs.extend(customParseFormat);
  import { useReportsApi } from './composables/reportsComposable';
  import CloudinaryQuillEditor from '../components/CloudinaryQuillEditor.vue';
  import { exportsApi } from '@/modules/accountancy/services/api';
  import { getUserCode } from '@/utils/auth';

  // Use composable for API operations
  const {
    loading,
    saving,
    searchReports,
    getReportDetail,
    saveReport,
    updateReport,
    deleteReport,
  } = useReportsApi();

  const typeView = ref('L');
  const searchText = ref('');
  const filterStatus = ref('ALL');
  const filterSearchType = ref('FECSEG');
  const startDate = ref(dayjs().subtract(1, 'month'));
  const endDate = ref(dayjs());
  const isEditing = ref(false);

  // Modal states
  const detailsModalOpen = ref(false);
  const selectedReport = ref(null);

  // Flags to track if fields had value when opening the form
  const hasInitialComment = ref(false);
  const hasInitialResponse = ref(false);
  const searchingDetail = ref(false);
  const fileDetailFound = ref(false);

  const formData = reactive({
    codref: null,
    nroref: '',
    codcli: '',
    clientName: '',
    reservationName: '',
    area: '',
    resume: '',
    codusu: '',
    userep: '',
    quien_reporto: 'RO2', // Default to OPERACIONES
    otruse: '', // Added for conditional input
    tipo_respuesta: 'RO1', // Default to INFORMATIVO
    comment: '',
    response: '',
    datein: null,
    dateout: null,
    usuario: getUserCode(),
    nroord: null,
    isClosed: false,
  });

  const columns = [
    {
      title: 'CÓDIGO REPORTE',
      dataIndex: 'reportCode',
      key: 'reportCode',
      width: 120,
      align: 'center',
    },
    { title: 'Nº FILE', dataIndex: 'fileNumber', key: 'fileNumber', width: 100, align: 'center' },
    { title: 'USUARIO', dataIndex: 'user', key: 'user', width: 100, align: 'center' },
    { title: 'CLIENTE', dataIndex: 'client', key: 'client', width: 180 },
    { title: 'NOMBRE DEL FILE', dataIndex: 'fileName', key: 'fileName', width: 200 },
    { title: 'KAM', dataIndex: 'kam', key: 'kam', width: 80, align: 'center' },
    { title: 'EJE. QR', dataIndex: 'ejeqr', key: 'ejeqr', width: 80, align: 'center' },
    {
      title: 'FECHA RECEPCION',
      dataIndex: 'receiptDate',
      key: 'receiptDate',
      width: 120,
      align: 'center',
    },
    {
      title: 'FECHA RESOLUCION',
      dataIndex: 'resolutionDate',
      key: 'resolutionDate',
      width: 120,
      align: 'center',
    },
    { title: 'DIAS GESTIÓN', dataIndex: 'mgmtDays', key: 'mgmtDays', width: 100, align: 'center' },
    { title: 'ESTADO', dataIndex: 'status', key: 'status', width: 130, align: 'center' },
    { title: '', key: 'actions', width: 140, align: 'center' },
  ];

  const dataSource = ref([]);

  const handleSearch = async () => {
    const params = {
      nrocom: searchText.value,
      state: filterStatus.value,
      tipo_fecha: filterSearchType.value,
      fecini: startDate.value ? startDate.value.format('DD/MM/YYYY') : '',
      fecfin: endDate.value ? endDate.value.format('DD/MM/YYYY') : '',
      format: '',
      usuario: getUserCode(),
    };
    const results = await searchReports(params);
    dataSource.value = results;
  };

  const handleSearchDetail = debounce(async () => {
    if (!formData.nroref) {
      fileDetailFound.value = false;
      return;
    }

    searchingDetail.value = true;
    fileDetailFound.value = false;
    try {
      const data = await getReportDetail(formData.nroref);
      if (data && (data.CODCLI || data.codcli)) {
        formData.codcli = data.CODCLI || data.codcli;
        formData.clientName = data.RAZON || data.razon;
        formData.codusu = data.CODOPE || data.codope;
        formData.reservationName = data.DESCRI || data.descri;
        formData.area = data.codsec || data.area || '';
        if (data.fecser) {
          formData.datein = dayjs(data.fecser, 'DD/MM/YYYY');
        }
        if (data.fecfin) {
          formData.dateout = dayjs(data.fecfin, 'DD/MM/YYYY');
        }
        fileDetailFound.value = true;
      } else {
        notification.warning({
          message: 'File no encontrado',
          description: `No se encontró información para el file ${formData.nroref}`,
        });
        fileDetailFound.value = false;
      }
    } catch (error) {
      console.error('Error searching detail:', error);
      notification.error({
        message: 'Error al buscar file',
        description: 'Ocurrió un error al buscar la información del file',
      });
      fileDetailFound.value = false;
    } finally {
      searchingDetail.value = false;
    }
  }, 350);

  const openCreation = () => {
    isEditing.value = false;
    resetForm();
    fileDetailFound.value = false;
    typeView.value = 'F';
  };

  const openEdit = async (record) => {
    console.log('Reporte: ', record);
    isEditing.value = true;
    Object.assign(formData, {
      codref: record.codref || record.key,
      nroref: record.fileNumber,
      codcli: record.codcli,
      clientName: record.client,
      reservationName: record.fileName,
      area: record.area,
      resume: record.subject,
      codusu: record.codusu,
      userep: record.userep || '',
      quien_reporto: record.userep || 'RO1', // USEREP maps to quien_reporto
      otruse: record.otruse || '',
      tipo_respuesta: record.optadi || 'RO1', // OPTADI maps to tipo_respuesta
      optadi: record.optadi || '',
      mgmtDays: record.dias || 0,
      comment: record.comment,
      response: record.respue,
      datein: record.fecser ? dayjs(record.fecser, 'DD/MM/YYYY') : null,
      dateout: record.fecfin ? dayjs(record.fecfin, 'DD/MM/YYYY') : null,
      nroord: record.nroord || null,
      isClosed: record.status?.toUpperCase().includes('CERRADO'),
    });

    // Set initial value flags
    hasInitialComment.value = !!record.comment && record.comment.trim() !== '';
    hasInitialResponse.value = !!record.respue && record.respue.trim() !== '';
    fileDetailFound.value = true; // Editing existing report, file is valid

    typeView.value = 'F';
  };

  const handleSave = async () => {
    // Validation: Close Date is mandatory if Response is filled OR Report is Closed
    const cleanResponse = formData.response ? formData.response.replace(/<[^>]*>/g, '').trim() : '';
    const hasResponse = cleanResponse.length > 0;

    if ((hasResponse || formData.isClosed) && !formData.dateout) {
      notification.warning({
        message: 'Falta Fecha de Cierre',
        description:
          'Debe ingresar la Fecha de Cierre si hay una respuesta o el reporte está cerrado.',
      });
      return;
    }

    const payload = {
      // Core fields
      nroref: formData.nroref,
      codcli: formData.codcli,
      comment: formData.comment,
      response: formData.response,

      // Dates
      datein: formData.datein ? formData.datein.format('DD/MM/YYYY') : '',
      dateout: formData.dateout ? formData.dateout.format('DD/MM/YYYY') : '',

      // Report Specific
      resume: formData.resume,
      userep: formData.userep,
      quien_reporto: formData.quien_reporto,
      otruse: formData.otruse,
      tipo_respuesta: formData.tipo_respuesta,
      optadi: formData.optadi,
      estado: formData.isClosed || formData.dateout ? 'CE' : 'PE', // "CE" = Cerrado, "PE" = En proceso

      usuario: getUserCode(),

      // Update specific
      codref: formData.codref,
      nroord: formData.nroord,
    };

    let success;
    if (isEditing.value) {
      success = await updateReport(payload);
    } else {
      const result = await saveReport(payload);
      success = !!result;
    }

    if (success) {
      typeView.value = 'L';
      handleSearch();
    }
  };

  const handleDeleteReport = (record) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar este reporte?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá recuperar la información.',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        const codref = record.codref || record.key;
        const success = await deleteReport(codref, getUserCode());
        if (success) {
          handleSearch();
        }
      },
      onCancel() {
        console.log('Cancel');
      },
    });
  };

  const resetForm = () => {
    Object.assign(formData, {
      codref: null,
      nroref: '',
      codcli: '',
      clientName: '',
      reservationName: '',
      area: '',
      resume: '',
      codusu: '',
      userep: '',
      quien_reporto: 'RO2',
      otruse: '',
      tipo_respuesta: 'RO1',
      comment: '',
      response: '',
      datein: dayjs(), // Default to current date
      dateout: null,
      nroord: null,
      isClosed: false,
      estado: 'PE', // Default to "En proceso"
    });
    hasInitialComment.value = false;
    hasInitialResponse.value = false;
  };

  const handleResponse = async (response, filename) => {
    const contentType = response.headers['content-type'];

    if (contentType && contentType.includes('application/json')) {
      const textData = await response.data.text();
      const jsonData = JSON.parse(textData);
      notification.info({
        message: 'Información',
        description: jsonData.text || 'El reporte se está procesando y llegará a su correo.',
      });
      return;
    }

    downloadFile(response.data, filename);
    notification.success({
      message: 'Éxito',
      description: 'Excel descargado correctamente',
    });
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

  const exportExcel = async () => {
    const date1 = startDate.value ? startDate.value.format('DD/MM/YYYY') : '';
    const date2 = endDate.value ? endDate.value.format('DD/MM/YYYY') : '';

    try {
      const params = {
        type: 'reports',
        usuario: getUserCode(),
        fecin: date1,
        fecout: date2,
        state: filterStatus.value,
        tipo_fecha: filterSearchType.value,
      };

      const response = await exportsApi.get('/excel', {
        params,
        responseType: 'blob',
      });

      await handleResponse(response, `reportes-${dayjs().format('YYYY-MM-DD')}.xlsx`);
    } catch (error) {
      console.error('Error exportExcel:', error);
      notification.error({
        message: 'Error',
        description: 'Error al procesar la solicitud del Excel',
      });
    }
  };

  const openDetailsModal = (record) => {
    selectedReport.value = record;
    detailsModalOpen.value = true;
  };

  const getCommentButtonClass = (record) => {
    const hasComment = record.comment && record.comment.trim();
    const hasResponse = record.respue && record.respue.trim();

    if (hasComment && hasResponse) {
      return 'btn-comment-complete'; // Verde - ambos
    } else if (hasComment && !hasResponse) {
      return 'btn-comment-pending'; // Amarillo - solo comentario
    } else {
      return 'btn-comment-empty'; // Gris - vacío
    }
  };

  onMounted(() => {
    handleSearch();
  });
</script>

<style scoped lang="scss">
  @import '@/scss/accountancy.scss';
</style>
