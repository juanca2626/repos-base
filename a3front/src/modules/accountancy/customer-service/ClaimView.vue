<template>
  <div class="classic-workspace-container">
    <!-- List View -->
    <div v-if="typeView === 'L'" class="fade-in">
      <div class="classic-top-bar">
        <div class="top-left">
          <a-button class="btn-classic-new" @click="openCreation">
            <template #icon><PlusOutlined /></template>
            Agregar Reclamo
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
              <a-button type="ghost" class="p-1" danger @click="handleDeleteClaim(record)">
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
              <p>No hay registros para mostrar</p>
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
            {{ isEditing ? 'Editar Reclamo #' + formData.codref : 'Nuevo Reclamo' }}
          </h2>
          <p class="form-subtitle">Complete la información detallada para la gestión del reclamo</p>
        </div>
      </div>

      <div class="classic-form-body-grid">
        <!-- Tabs for Additional Info -->
        <div class="form-tabs-container" v-if="isEditing">
          <a-tabs v-model:activeKey="activeTab" type="card">
            <a-tab-pane key="details" tab="Detalle del Reclamo">
              <a-form layout="vertical" class="premium-classic-form">
                <div class="form-section-card">
                  <a-row :gutter="32">
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>NÚMERO DE FILE</label>
                        <a-input
                          class="no-border"
                          v-model:value="formData.nroref"
                          placeholder="Ej: 359177"
                          @blur="handleSearchDetail"
                          :loading="searchingDetail"
                        >
                          <template #suffix>
                            <LoadingOutlined v-if="searchingDetail" />
                          </template>
                        </a-input>
                      </div>
                      <div class="classic-form-item">
                        <label>NOMBRE RESERVA</label>
                        <a-input
                          v-model:value="formData.reservationName"
                          placeholder="Nombre completo"
                        />
                      </div>
                      <div class="classic-form-item">
                        <label>CLIENTE</label>
                        <a-input
                          v-model:value="formData.clientName"
                          placeholder="Nombre del cliente"
                        />
                      </div>
                      <div class="classic-form-item">
                        <label>ÁREA</label>
                        <a-input v-model:value="formData.area" placeholder="Ej: Operaciones" />
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>ESPECIALISTA</label>
                        <a-input
                          v-model:value="formData.specialist"
                          placeholder="Nombre del especialista"
                        />
                      </div>
                    </a-col>
                  </a-row>
                  <div class="classic-form-item">
                    <label>COMENTARIO</label>
                    <div v-if="!hasInitialComment" class="quill-premium-wrapper">
                      <CloudinaryQuillEditor
                        v-model="formData.comment"
                        :style="{ height: '180px' }"
                      />
                    </div>
                    <div v-else class="readonly-content-box" v-html="formData.comment"></div>
                  </div>

                  <div class="classic-form-item">
                    <label>RESPUESTA</label>
                    <div v-if="!hasInitialResponse" class="quill-premium-wrapper">
                      <CloudinaryQuillEditor
                        v-model="formData.response"
                        :style="{ height: '180px' }"
                      />
                    </div>
                    <div v-else class="readonly-content-box" v-html="formData.response"></div>
                  </div>
                  <div class="classic-form-item">
                    <a-checkbox v-model:checked="formData.isInfounded"
                      >EL RECLAMO ES INFUNDADO</a-checkbox
                    >
                  </div>
                  <div class="classic-form-item">
                    <a-checkbox v-model:checked="formData.isClosed">RECLAMO CERRADO</a-checkbox>
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

                  <a-row :gutter="32">
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>MONTO COMPENSACIÓN</label>
                        <a-input
                          v-model:value="formData.monto_compensacion"
                          type="number"
                          prefix="$"
                          class="w-full no-border"
                          min="0"
                        />
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>OBSERVACIONES COMPENSACIÓN</label>
                        <a-input
                          v-model:value="formData.observaciones_compensacion"
                          placeholder="Detalle de la compensación..."
                        />
                      </div>
                    </a-col>
                  </a-row>

                  <a-row :gutter="32">
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>MONTO REEMBOLSO</label>
                        <a-input
                          v-model:value="formData.monto_reembolso"
                          type="number"
                          prefix="$"
                          class="w-full no-border"
                          min="0"
                        />
                      </div>
                    </a-col>
                    <a-col :span="12">
                      <div class="classic-form-item">
                        <label>OBSERVACIONES REEMBOLSO</label>
                        <a-input
                          v-model:value="formData.observaciones_reembolso"
                          placeholder="Detalle del reembolso..."
                        />
                      </div>
                    </a-col>
                  </a-row>

                  <div class="classic-form-item">
                    <label>MONTO DEVOLUCIÓN TOTAL</label>
                    <a-input
                      v-model:value="formData.total_compensacion"
                      type="number"
                      prefix="$"
                      class="w-full no-border"
                      min="0"
                      readonly
                      style="background-color: #f8fafc"
                    />
                  </div>
                </div>
              </a-form>
            </a-tab-pane>

            <a-tab-pane
              key="replies"
              :tab="`Réplicas ${repliesList.length > 0 ? `(${repliesList.length})` : ''}`"
            >
              <RepliesTab
                :codref="formData.codref"
                :nrocom="currentNrocom"
                :replies="repliesList"
                @refresh="fetchTabDetails"
              />
            </a-tab-pane>

            <a-tab-pane
              key="services"
              :tab="`Códigos de Servicio ${serviceCodesList.length > 0 ? `(${serviceCodesList.length})` : ''}`"
            >
              <ServiceCodesTab
                :codref="formData.codref"
                :nrocom="currentNrocom"
                :service-codes="serviceCodesList"
                @refresh="fetchTabDetails"
              />
            </a-tab-pane>
          </a-tabs>
        </div>

        <!-- Main Form Area (Scrollable) - Show only form when NOT editing (New Claim) or fallback -->
        <div class="form-main-scrollable" v-else>
          <a-form layout="vertical" class="premium-classic-form">
            <div class="form-section-card">
              <a-row :gutter="32">
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>NÚMERO DE FILE</label>
                    <a-input
                      class="no-border"
                      v-model:value="formData.nroref"
                      placeholder="Ej: 359177"
                      @blur="handleSearchDetail"
                      :loading="searchingDetail"
                    >
                      <template #suffix>
                        <LoadingOutlined v-if="searchingDetail" />
                      </template>
                    </a-input>
                  </div>
                  <div class="classic-form-item">
                    <label>NOMBRE RESERVA</label>
                    <a-input
                      v-model:value="formData.reservationName"
                      placeholder="Nombre completo"
                    />
                  </div>
                  <div class="classic-form-item">
                    <label>CLIENTE</label>
                    <a-input v-model:value="formData.clientName" placeholder="Nombre del cliente" />
                  </div>
                  <div class="classic-form-item">
                    <label>ÁREA</label>
                    <a-input v-model:value="formData.area" placeholder="Ej: Operaciones" />
                  </div>
                </a-col>
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>ESPECIALISTA</label>
                    <a-input
                      v-model:value="formData.specialist"
                      placeholder="Nombre del especialista"
                    />
                  </div>
                </a-col>
              </a-row>
              <div class="classic-form-item">
                <label>COMENTARIO</label>
                <div v-if="!hasInitialComment" class="quill-premium-wrapper">
                  <CloudinaryQuillEditor v-model="formData.comment" :style="{ height: '180px' }" />
                </div>
                <div v-else class="readonly-content-box" v-html="formData.comment"></div>
              </div>

              <div class="classic-form-item">
                <label>RESPUESTA</label>
                <div v-if="!hasInitialResponse" class="quill-premium-wrapper">
                  <CloudinaryQuillEditor v-model="formData.response" :style="{ height: '180px' }" />
                </div>
                <div v-else class="readonly-content-box" v-html="formData.response"></div>
              </div>
              <div class="classic-form-item">
                <a-checkbox v-model:checked="formData.isInfounded"
                  >EL RECLAMO ES INFUNDADO</a-checkbox
                >
              </div>
              <div class="classic-form-item">
                <a-checkbox v-model:checked="formData.isClosed">RECLAMO CERRADO</a-checkbox>
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

              <a-row :gutter="32">
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>MONTO COMPENSACIÓN</label>
                    <a-input
                      v-model:value="formData.monto_compensacion"
                      type="number"
                      prefix="$"
                      class="w-full no-border"
                      min="0"
                    />
                  </div>
                </a-col>
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>OBSERVACIONES COMPENSACIÓN</label>
                    <a-input
                      v-model:value="formData.observaciones_compensacion"
                      placeholder="Detalle de la compensación..."
                    />
                  </div>
                </a-col>
              </a-row>

              <a-row :gutter="32">
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>MONTO REEMBOLSO</label>
                    <a-input
                      v-model:value="formData.monto_reembolso"
                      type="number"
                      prefix="$"
                      class="w-full no-border"
                      min="0"
                    />
                  </div>
                </a-col>
                <a-col :span="12">
                  <div class="classic-form-item">
                    <label>OBSERVACIONES REEMBOLSO</label>
                    <a-input
                      v-model:value="formData.observaciones_reembolso"
                      placeholder="Detalle del reembolso..."
                    />
                  </div>
                </a-col>
              </a-row>

              <div class="classic-form-item">
                <label>MONTO DEVOLUCIÓN TOTAL</label>
                <a-input
                  v-model:value="formData.total_compensacion"
                  type="number"
                  prefix="$"
                  class="w-full no-border"
                  min="0"
                  readonly
                  style="background-color: #f8fafc"
                />
              </div>
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
                Guardar Reclamo
              </a-button>
              <template v-if="isEditing">
                <a-button class="btn-board red" @click="openProductNonConforming">
                  <template #icon><PlusCircleOutlined /></template>
                  Producto No Conforme
                </a-button>
              </template>
            </div>

            <div class="board-footer">
              <a-checkbox v-model:checked="formData.flag_notify" :true-value="0" :false-value="1">
                IGNORAR LAS NOTIFICACIONES POR CORREO
              </a-checkbox>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <!-- Product Non-Conforming Modal -->
    <ProductNonConformingModal
      v-model:open="productNCModalOpen"
      :nrofile="formData.nroref"
      @saved="handleProductNCSaved"
    />

    <!-- Details Modal -->
    <a-modal
      v-model:open="detailsModalOpen"
      title="Detalles del Reclamo"
      width="900px"
      :footer="null"
    >
      <div class="details-modal-content">
        <div class="detail-section" v-if="selectedClaim?.comment">
          <h4 class="detail-section-title"><CommentOutlined /> COMENTARIO</h4>
          <div class="detail-content" v-html="selectedClaim?.comment"></div>
        </div>

        <div class="detail-section" v-if="selectedClaim?.respue">
          <h4 class="detail-section-title"><CheckCircleOutlined /> RESPUESTA</h4>
          <div class="detail-content" v-html="selectedClaim?.respue"></div>
        </div>
      </div>
    </a-modal>
  </div>
</template>

<script setup>
  import { ref, reactive, onMounted, watch, createVNode, computed } from 'vue';
  import {
    SearchOutlined,
    CloudDownloadOutlined,
    PlusOutlined,
    InboxOutlined,
    ArrowLeftOutlined,
    SaveOutlined,
    LoadingOutlined,
    DeleteOutlined,
    PlusCircleOutlined,
    EditOutlined,
    CheckCircleOutlined,
    CommentOutlined,
    ExclamationCircleOutlined,
  } from '@ant-design/icons-vue';
  import { notification, Modal } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { debounce } from 'lodash-es';
  import customParseFormat from 'dayjs/plugin/customParseFormat';
  dayjs.extend(customParseFormat);
  import { useClaimsApi } from './composables/claimsComposable';
  import CloudinaryQuillEditor from '../components/CloudinaryQuillEditor.vue';
  import RepliesTab from './components/RepliesTab.vue';
  import ServiceCodesTab from './components/ServiceCodesTab.vue';
  import { exportsApi } from '@/modules/accountancy/services/api';
  import ProductNonConformingModal from './components/ProductNonConformingModal.vue';
  import { getUserCode } from '@/utils/auth';

  // Use composable for API operations
  const {
    loading,
    saving,
    searchClaims,
    getClaimDetail,
    saveClaim,
    updateClaim,
    deleteClaim,
    transformClaimData,
    getReplies,
    getServiceCodes,
  } = useClaimsApi();

  const typeView = ref('L');
  const searchText = ref('');
  const filterStatus = ref('ALL');
  const filterSearchType = ref('FECSEG');
  const startDate = ref(dayjs().subtract(1, 'month'));
  const endDate = ref(dayjs());
  const searchingDetail = ref(false);
  const isEditing = ref(false);
  const activeTab = ref('details');

  // Tab data
  const repliesList = ref([]);
  const serviceCodesList = ref([]);

  // Modal states
  const productNCModalOpen = ref(false);
  const detailsModalOpen = ref(false);
  const selectedClaim = ref(null);
  const fileDetailFound = ref(false);

  // Computed for nrocom to pass to tabs
  const currentNrocom = computed(() => {
    if (!formData.codref) return '';
    return String(formData.codref).padStart(9, '0') + 'C';
  });

  // Flags to track if fields had value when opening the form
  const hasInitialComment = ref(false);
  const hasInitialResponse = ref(false);

  // Function to fetch tab details
  const fetchTabDetails = async () => {
    if (!currentNrocom.value) return;

    // Concurrently fetch replies and service codes
    const [replies, codes] = await Promise.all([
      getReplies(currentNrocom.value),
      getServiceCodes(currentNrocom.value),
    ]);

    repliesList.value = replies || [];
    serviceCodesList.value = (codes || []).map((item) => ({
      clave: item.clave,
      nrolin: item.nrolin,
      codsvs: item.codsvs || '',
      texto: item.texto || '',
      fecha: item.fecha,
      hora: item.hora,
    }));
  };

  const formData = reactive({
    codref: null,
    nroref: '',
    codcli: '',
    clientName: '',
    reservationName: '',
    specialist: '',
    area: '',
    comment: '',
    response: '',
    datein: null,
    dateout: null,
    tipo_reclamo: null,
    estado_reclamo: null,
    tipo_monto: null,
    compensacion_reembolso: '',
    total_compensacion: 0,
    monto_compensacion: 0,
    monto_reembolso: 0,
    observaciones_compensacion: '',
    observaciones_reembolso: '',
    observaciones_reembolso: '',
    usuario: getUserCode(),
    isInfounded: false,
    isClosed: false,
    nroord: null,
    flag_notify: 0,
  });

  // Watch for changes in compensation and reimbursement to update total
  watch(
    () => [formData.monto_compensacion, formData.monto_reembolso],
    ([newComp, newReemb]) => {
      const comp = parseFloat(newComp) || 0;
      const reemb = parseFloat(newReemb) || 0;
      formData.total_compensacion = comp + reemb;
    }
  );

  const columns = [
    {
      title: 'CÓDIGO QUEJA',
      dataIndex: 'claimCode',
      key: 'claimCode',
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
    { title: 'TIPO', dataIndex: 'type', key: 'type', width: 100, align: 'center' },
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
    const results = await searchClaims(params);
    dataSource.value = results.map(transformClaimData);
  };

  const handleSearchDetail = debounce(async () => {
    if (!formData.nroref) {
      fileDetailFound.value = false;
      return;
    }

    searchingDetail.value = true;
    fileDetailFound.value = false;
    try {
      const data = await getClaimDetail(formData.nroref);
      if (data && (data.CODCLI || data.codcli)) {
        formData.codcli = data.CODCLI || data.codcli;
        formData.clientName = data.RAZON || data.razon;
        formData.reservationName = data.DESCRI || data.descri;
        formData.area = data.codsec || data.area || '';
        formData.specialist = data.CODOPE || data.codope;
        if (data.fecser) {
          const dateValue = data.fecser;
          formData.datein = dayjs(dateValue, 'DD/MM/YYYY');
        }
        if (data.fecfin) {
          const dateValue = data.fecfin;
          formData.dateout = dayjs(dateValue, 'DD/MM/YYYY');
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
    repliesList.value = [];
    serviceCodesList.value = [];
    fileDetailFound.value = false;
    typeView.value = 'F';
  };

  const openEdit = (record) => {
    isEditing.value = true;
    Object.assign(formData, {
      codref: record.codref,
      nroref: record.fileNumber,
      clientName: record.client,
      reservationName: record.fileName,
      comment: record.comment || '',
      response: record.respue || '',
      datein: record.fecser ? dayjs(record.fecser, 'DD/MM/YYYY') : null,
      dateout: record.fecfin ? dayjs(record.fecfin, 'DD/MM/YYYY') : null,
      tipo_monto: record.tipo_monto || null,
      compensacion_reembolso: record.compensacion_reembolso || '',
      total_compensacion: record.total_compensacion || 0,
      monto_compensacion: record.monto_compensacion || 0,
      monto_reembolso: record.monto_reembolso || 0,
      observaciones_compensacion: record.observaciones_compensacion || '',
      observaciones_reembolso: record.observaciones_reembolso || '',
      status: record.status,
      area: record.area || '',
      specialist: record.specialist || '',
      isInfounded: record.type?.toUpperCase() === 'INFUNDADO',
      isClosed: record.status?.toUpperCase().includes('CERRADO'),
      nroord: record.nroord || null,
    });

    // Set initial value flags
    hasInitialComment.value = !!record.comment && record.comment.trim() !== '';
    hasInitialResponse.value = !!record.respue && record.respue.trim() !== '';
    fileDetailFound.value = true; // Editing existing claim, file is valid

    typeView.value = 'F';

    // Load tab details
    fetchTabDetails();
  };

  const handleSave = async () => {
    // Validation: Close Date is mandatory if Response is filled OR Claim is Closed
    // Strip HTML tags to check for real content
    const cleanResponse = formData.response ? formData.response.replace(/<[^>]*>/g, '').trim() : '';
    const hasResponse = cleanResponse.length > 0;

    if ((hasResponse || formData.isClosed) && !formData.dateout) {
      notification.warning({
        message: 'Falta Fecha de Cierre',
        description:
          'Debe ingresar la Fecha de Cierre si hay una respuesta o el reclamo está cerrado.',
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

      // Logic fields
      tipo_reclamo: formData.isInfounded ? 'FK' : 'OK',
      estado_reclamo: formData.isClosed || formData.dateout ? 'CE' : 'PE', // "CE" = Cerrado, "PE" = En proceso

      // Compensation fields
      tipo_monto: formData.tipo_monto,
      compensacion_reembolso: formData.compensacion_reembolso,
      total_compensacion: formData.total_compensacion,
      monto_compensacion: formData.monto_compensacion,
      monto_reembolso: formData.monto_reembolso,
      monto_reclamo: formData.total_compensacion,
      observaciones_compensacion: formData.observaciones_compensacion,
      observaciones_reembolso: formData.observaciones_reembolso,

      // Context/Meta
      usuario: getUserCode(),

      // Update-specific
      codref: formData.codref,
      nroord: formData.nroord,
      flag_notify: formData.flag_notify,
    };

    let success;
    if (isEditing.value) {
      success = await updateClaim(payload);
    } else {
      const result = await saveClaim(payload);
      success = !!result;
      if (result) {
        formData.codref = result.codref;
      }
    }

    if (success) {
      typeView.value = 'L';
      handleSearch();
    }
  };

  const handleDeleteClaim = (record) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar este reclamo?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá recuperar la información.',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        const codref = record.codref || record.key;
        const success = await deleteClaim(codref, getUserCode());
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
      specialist: '',
      area: '',
      comment: '',
      response: '',
      datein: dayjs(), // Default to current date (arrival date)
      dateout: null,
      tipo_reclamo: null,
      estado_reclamo: 'PE', // Default to "En proceso"
      tipo_monto: null,
      compensacion_reembolso: '',
      total_compensacion: 0,
      monto_compensacion: 0,
      monto_reembolso: 0,
      observaciones_compensacion: '',
      observaciones_reembolso: '',
      isInfounded: false,
      isClosed: false,
      nroord: null,
      flag_notify: 0,
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
        type: 'claims',
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

      await handleResponse(response, `reclamos-${dayjs().format('YYYY-MM-DD')}.xlsx`);
    } catch (error) {
      console.error('Error exportExcel:', error);
      notification.error({
        message: 'Error',
        description: 'Error al procesar la solicitud del Excel',
      });
    }
  };

  const openProductNonConforming = () => {
    if (!formData.nroref) {
      notification.warning({
        message: 'Advertencia',
        description: 'Debe ingresar un número de file primero',
      });
      return;
    }
    productNCModalOpen.value = true;
  };

  const handleProductNCSaved = () => {
    notification.success({
      message: 'Éxito',
      description: 'Producto No Conforme creado exitosamente',
    });
    productNCModalOpen.value = false;
  };

  const openDetailsModal = (record) => {
    selectedClaim.value = record;
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

  .modernized-classic-table {
    :deep(.ant-table-tbody > tr > td) {
      padding: 1px !important;
    }
  }
</style>
