<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Gestión de Rendiciones</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-row type="flex" justify="space-between" align="middle" style="gap: 10px">
          <a-col :span="18">
            <a-form :model="filters" layout="vertical">
              <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Estado:" name="value" class="mb-0">
                    <a-select
                      v-model:value="filters.status"
                      placeholder="Seleccione un estado"
                      :show-search="false"
                      :allow-clear="false"
                      :options="states"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="3">
                  <a-form-item label="File:" name="value" class="mb-0">
                    <a-input
                      autocomplete="off"
                      type="number"
                      v-model:value="filters.term"
                      placeholder="Ingrese un file.."
                    />
                  </a-form-item>
                </a-col>
                <a-col>
                  <a-button type="primary" :disabled="isLoading" @click="fetchEffective">
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
        </a-row>
      </div>
    </div>

    <!-- Tabla -->
    <backend-table-component
      ref="tableRef"
      :items="effective"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      @change="handleChange"
    >
      <template #status="{ record }">
        <span class="text-600">{{ getStatus(record.status) }}</span>
      </template>

      <template #guideCode="{ record }">
        <span
          ><b>{{ record.guideCode }}</b></span
        >
      </template>

      <template #startDate="{ record }">
        <span>
          <b>{{ record.startDate }}</b></span
        >
      </template>

      <template #summary="{ record }">
        <span>{{ record.summary }}</span>
        <template v-if="record.summary !== 0">
          <span class="d-block"><i>Sobrante</i></span>
        </template>
      </template>

      <template #actions="{ record }">
        <a-button type="primary" @click="openModal(record)">
          <ExclamationCircleOutlined />
          <template v-if="isProvider">Iniciar Rendición</template>
          <template v-else>Ver</template>
        </a-button>
      </template>
    </backend-table-component>
  </div>

  <a-modal
    v-model:visible="showModal"
    title="VER RENDICIÓN"
    width="1140px"
    :ok-button-props="{ hidden: true }"
    @cancel="handleCancel"
    cancelText="CERRAR"
  >
    <template v-if="view === 'items' && cash">
      <div>
        <p class="text-center">{{ cash.templateName }}</p>
      </div>

      <div class="info-container">
        <a-descriptions
          bordered
          size="small"
          :column="{ xxl: 1, xl: 1, lg: 1, md: 1, sm: 1, xs: 1 }"
        >
          <a-descriptions-item label="DURACIÓN">
            <template #default>{{ cash.template.durationDays }} día(s)</template>
          </a-descriptions-item>

          <a-descriptions-item label="CANTIDAD">
            <a-typography-text strong>{{ cash.paxCount }} pax(s)</a-typography-text>
          </a-descriptions-item>

          <a-descriptions-item label="TIPO">
            <a-tag color="#dc3545">{{ cash.typeName }}</a-tag>
          </a-descriptions-item>

          <a-descriptions-item label="FILE OPE">
            <a-typography-text class="text-red-bold">{{ cash.opeFile }}</a-typography-text>
          </a-descriptions-item>

          <a-descriptions-item label="FILES COMERCIALES" :span="2">
            <a-badge
              status="processing"
              text="En revisión"
              v-if="cash.commercialFiles.length === 0"
            />
            <template v-for="file in cash.commercialFiles">
              <a-tag color="#dc3545">{{ file }}</a-tag>
            </template>
          </a-descriptions-item>
        </a-descriptions>
      </div>

      <div class="mt-3">
        <backend-table-component
          ref="tableRefItems"
          :items="cash.items"
          :columns="columnsItems"
          :options="tableOptionsItems"
          size="small"
          :rowClassName="rowClassName"
          @change="handleChange"
        >
          <template #startDate>
            <b>{{ cash.startDate }}</b>
          </template>
          <template #categoryName="{ record }">
            <span>{{ record.categoryName }}</span>
          </template>
          <template #serviceName="{ record }">
            <span>{{ record.serviceName }}</span>
          </template>
          <template #documents="{ record }">
            <template v-if="record.unitCost > 0">
              <a-button type="primary" @click="showDocuments(record)">
                ({{ record.expenseReports.length }}) <ArrowRightOutlined />
              </a-button>
            </template>
            <template v-else> - </template>
          </template>
        </backend-table-component>
      </div>
    </template>

    <template v-if="view === 'documents'">
      <div>
        <p class="text-center mb-0">{{ item.serviceName }}</p>
        <p class="text-center mb-0">{{ item.categoryName }}</p>
      </div>

      <div>
        <template v-if="isProvider">
          <a-row
            type="flex"
            justify="space-between"
            align="middle"
            class="filters-section p-3 my-4"
            style="gap: 10px"
          >
            <a-col>
              <a-row type="flex" justify="start" align="bottom" style="gap: 20px">
                <a-col>
                  <a-form layout="vertical">
                    <a-form-item label="Tipo de Documento:" name="value" class="mb-0">
                      <a-select
                        v-model:value="documentType"
                        style="width: 310px"
                        :options="documentTypes"
                      />
                      <template #option="{ option }">
                        <small>{{ option.label }}</small>
                      </template>
                    </a-form-item>
                  </a-form>
                </a-col>
                <a-col>
                  <a-button type="primary" @click="addNewItem">Agregar Comprobante</a-button>
                </a-col>
              </a-row>
            </a-col>
            <a-col>
              <div class="text-center">
                <p class="mb-0">TOTAL:</p>
                <h3 class="header-title mb-0">{{ item.total }} {{ item.currency }}</h3>
              </div>
            </a-col>
            <a-col>
              <div class="text-center">
                <p class="mb-0">COSTO REAL:</p>
                <h3 class="header-title mb-0">{{ item.realTotal }} {{ item.currency }}</h3>
              </div>
            </a-col>
            <a-col>
              <div class="text-center">
                <p class="mb-0">DIFERENCIA:</p>
                <h3 class="header-title mb-0">{{ item.difference }} {{ item.currency }}</h3>
              </div>
            </a-col>
          </a-row>

          <a-collapse
            v-model:activeKey="activeKey"
            accordion
            expand-icon-position="right"
            v-if="newItems.length > 0"
          >
            <template v-for="(report, r) in newItems" :key="`new-item-${r}`">
              <a-collapse-panel>
                <template #header>
                  <a-row
                    type="flex"
                    justify="space-between"
                    align="middle"
                    class="header-container"
                  >
                    <a-col>
                      <a-typography-text strong
                        >{{ getDocumentType(report.documentType) }}:</a-typography-text
                      >
                    </a-col>
                    <a-col v-if="report.status === 'PENDING'">
                      <a-tooltip title="Eliminar documento">
                        <a-button type="default" danger size="small" @click.stop="deleteNewItem(r)">
                          <DeleteOutlined />
                        </a-button>
                      </a-tooltip>
                    </a-col>
                  </a-row>
                </template>

                <a-form layout="vertical" :model="report">
                  <a-row :gutter="16">
                    <a-col :xs="24" :sm="8" :md="4">
                      <a-form-item label="FECHA EMISIÓN:">
                        <a-date-picker
                          v-model:value="report.issueDate"
                          placeholder="DD/MM/YYYY"
                          format="DD/MM/YYYY"
                          :disabled="report.status !== 'PENDING'"
                          value-format="YYYY-MM-DD"
                          style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="4">
                      <a-form-item label="RUC:">
                        <a-input
                          v-model:value="report.ruc"
                          :disabled="report.status !== 'PENDING'"
                          placeholder="RUC"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="4">
                      <a-form-item label="SERIE:">
                        <a-input
                          v-model:value="report.series"
                          :disabled="report.status !== 'PENDING'"
                          placeholder="Serie"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="4">
                      <a-form-item label="NRO. DOCUMENTO:">
                        <a-input
                          v-model:value="report.documentNumber"
                          :disabled="report.status !== 'PENDING'"
                          placeholder="Nro Documento"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="12" :sm="6" :md="2">
                      <a-form-item label="SUBTOTAL:" v-if="isIGV(report)">
                        <a-input-number
                          v-model:value="report.subtotal"
                          :disabled="report.status !== 'PENDING'"
                          :min="0"
                          style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="12" :sm="6" :md="2">
                      <a-form-item label="IGV:" v-if="isIGV(report)">
                        <a-input-number
                          v-model:value="report.igv"
                          :min="0"
                          :disabled="report.status !== 'PENDING'"
                          style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>

                    <a-col :xs="24" :sm="8" :md="4">
                      <a-form-item label="TOTAL:">
                        <a-input-number
                          v-model:value="report.total"
                          :min="0"
                          :disabled="report.status !== 'PENDING'"
                          style="width: 100%"
                        />
                      </a-form-item>
                    </a-col>
                  </a-row>

                  <a-row class="upload-section">
                    <a-col :span="24">
                      <a-form-item label="IMÁGENES DEL DOCUMENTO">
                        <a-alert type="warning" v-if="report.status === 'PENDING'">
                          <template #description>
                            <p class="mb-0"><strong class="text-dark">IMPORTANTE:</strong></p>
                            <p class="mb-0">* Los archivos deben estar en formato PDF o Imagen.</p>
                            <p class="mb-0">* El tamaño máximo por archivo es de 10MB.</p>
                          </template>
                        </a-alert>
                        <FileUploadComponent
                          v-if="cash"
                          title="Subir documentos del comprobante"
                          :links="report.images"
                          :editable="report.status === 'PENDING'"
                          :folder="`ope/${cash.receiptNumber}`"
                          @onResponseFiles="updateImages($event, report)"
                        />
                      </a-form-item>
                    </a-col>
                  </a-row>
                </a-form>
              </a-collapse-panel>
            </template>
          </a-collapse>
        </template>
        <template v-else>
          <a-collapse class="mt-3" v-model:activeKey="activeKey" expand-icon-position="right">
            <template v-for="(report, r) in item.expenseReports" :key="`report-${r}`">
              <a-collapse-panel>
                <template #header>
                  <div class="custom-header">
                    <span
                      ><b>{{ getDocumentType(report.documentType) }}</b> -
                      {{ report.documentNumber }}</span
                    >
                  </div>
                </template>

                <div class="panel-content">
                  <a-form layout="vertical" :model="report">
                    <a-row>
                      <a-col class="mb-2">
                        <a-checkbox
                          v-model:checked="report.isForPurchase"
                          :disabled="report.status !== 'PENDING'"
                          @click.stop
                        >
                          <span>VA A COMPRAS</span>
                        </a-checkbox>
                      </a-col>
                    </a-row>
                    <a-row>
                      <a-col :span="24">
                        <a-form-item
                          label="VALIDAR RUC"
                          :loading="isLoading"
                          :extra="
                            !report.isVerified && report.status === 'PENDING'
                              ? 'Verificar RUC para confirmar proveedor.'
                              : ''
                          "
                          name="ruc"
                          :class="!report.isVerified && report.status === 'PENDING' ? 'mb-2' : ''"
                        >
                          <a-input-search
                            :disabled="report.status !== 'PENDING' || report.isVerified"
                            v-model:value="report.ruc"
                            placeholder="00000000000"
                            enter-button="VERIFICAR"
                            size="large"
                            @search="handleVerifyRuc(report)"
                          >
                            <template #prefix><number-outlined /></template>
                          </a-input-search>
                        </a-form-item>
                      </a-col>

                      <template v-if="report.isSearch">
                        <a-col :xs="24" v-if="rucs.length > 0">
                          <a-form-item>
                            <a-select
                              placeholder="Seleccione un Proveedor"
                              :show-search="false"
                              :allow-clear="false"
                              size="large"
                              :options="rucs"
                              :disabled="report.isVerified"
                              @change="handleStatusChange($event, report)"
                            >
                            </a-select>
                          </a-form-item>
                        </a-col>
                        <a-col :xs="24" v-else>
                          <a-form-item class="mb-0">
                            <a-alert type="error">
                              <template #description>
                                <p class="text-danger mb-0">
                                  * No se encontraron proveedores con el RUC proporcionado.
                                </p>
                              </template>
                            </a-alert>
                          </a-form-item>
                        </a-col>
                      </template>
                    </a-row>

                    <a-row :gutter="24">
                      <a-col :xs="24" :sm="8">
                        <a-form-item label="FECHA EMISIÓN" name="issueDate">
                          <a-date-picker
                            v-model:value="report.issueDate"
                            style="width: 100%"
                            size="large"
                            :disabled="report.status !== 'PENDING' || !isProvider"
                            placeholder="Seleccione fecha"
                            format="DD/MM/YYYY"
                            value-format="YYYY-MM-DD"
                          />
                        </a-form-item>
                      </a-col>
                      <a-col :xs="24" :sm="8">
                        <a-form-item label="SERIE" name="series">
                          <a-input
                            v-model:value="report.series"
                            size="large"
                            :disabled="report.status !== 'PENDING' || !isProvider"
                            placeholder="0000"
                          />
                        </a-form-item>
                      </a-col>
                      <a-col :xs="24" :sm="8">
                        <a-form-item label="NRO. DOCUMENTO" name="documentNumber">
                          <a-input
                            size="large"
                            v-model:value="report.documentNumber"
                            :disabled="report.status !== 'PENDING' || !isProvider"
                            placeholder="144010"
                          />
                        </a-form-item>
                      </a-col>
                    </a-row>

                    <a-row class="upload-section" v-if="report.images.length > 0">
                      <a-col :span="24">
                        <a-form-item label="IMÁGENES DEL DOCUMENTO">
                          <FileUploadComponent
                            v-if="cash"
                            title="Subir documentos del comprobante"
                            :links="report.images"
                            :folder="`ope/${cash.receiptNumber}`"
                            :editable="report.isPendingSubmission || report.isNew ? true : false"
                            @onResponseFiles="updateImages($event, report)"
                          />
                        </a-form-item>
                      </a-col>
                    </a-row>

                    <a-row>
                      <a-col :span="24">
                        <a-form-item label="OBSERVACIONES" name="observation" class="mb-0">
                          <a-textarea
                            v-model:value="report.observation"
                            placeholder="Añadir las observaciones que sean necesarias..."
                            :rows="4"
                            :disabled="report.status !== 'PENDING'"
                            show-count
                            :maxlength="500"
                          />
                        </a-form-item>
                      </a-col>
                    </a-row>

                    <div class="action-buttons mt-0" v-if="report.status === 'PENDING'">
                      <a-button
                        type="primary"
                        size="large"
                        block
                        :class="report.isVerified ? 'btn-validate' : ''"
                        :disabled="!report.isVerified"
                        @click="handleValidate('report', report)"
                      >
                        <template #icon><CheckCircleOutlined /></template>
                        VALIDAR DOCUMENTO
                      </a-button>

                      <a-button
                        type="primary"
                        danger
                        size="large"
                        block
                        :class="report.isVerified ? 'btn-invalidate' : ''"
                        :disabled="!report.isVerified"
                        @click="handleInvalidate('report', report)"
                      >
                        <template #icon><CloseCircleOutlined /></template>
                        INVALIDAR DOCUMENTO
                      </a-button>
                    </div>
                  </a-form>
                </div>
              </a-collapse-panel>
            </template>
          </a-collapse>
        </template>
      </div>

      <div class="d-flex mt-3" style="gap: 10px">
        <template
          v-if="isProvider && newItems.filter((item: any) => item.status === 'PENDING').length > 0"
        >
          <a-button type="primary" :loading="isLoading" @click="handleSaveDocuments"
            >REGISTRAR COMPROBANTES</a-button
          >
        </template>
        <a-button type="default" :loading="isLoading" @click="handleBack">REGRESAR</a-button>
      </div>
    </template>

    <template
      v-if="
        view === 'items' &&
        !isProvider &&
        cash?.status !== 'VALIDATED' &&
        cash?.status !== 'REJECTED'
      "
    >
      <div
        class="action-buttons"
        v-if="cash?.items.filter((item: any) => item.expenseReports.length > 0).length > 0"
      >
        <a-button
          type="primary"
          size="large"
          block
          :loading="isLoading"
          class="btn-validate"
          @click="handleValidate('item', item)"
        >
          <template #icon><CheckCircleOutlined /></template>
          VALIDAR REQUERIMIENTO
        </a-button>

        <a-button
          type="primary"
          danger
          size="large"
          block
          :loading="isLoading"
          class="btn-invalidate"
          @click="handleInvalidate('item', item)"
        >
          <template #icon><CloseCircleOutlined /></template>
          INVALIDAR REQUERIMIENTO
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, onBeforeMount, computed } from 'vue';
  import { getUserType } from '@/utils/auth';
  import { useEffective, useDepartures } from '@/composables/adventure';
  import { Modal, notification } from 'ant-design-vue';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import {
    ReloadOutlined,
    SearchOutlined,
    ArrowRightOutlined,
    CheckCircleOutlined,
    CloseCircleOutlined,
    ExclamationCircleOutlined,
    DeleteOutlined,
  } from '@ant-design/icons-vue';
  import { debounce } from 'lodash';
  import FileUploadComponent from '@/components/global/FileUploadComponent.vue';
  import moment from 'moment';

  const isProvider = computed(() => {
    return getUserType() === 'GUI' || getUserType() === 'TRP';
  });

  const documentType = ref('INVOICE');

  const documentTypes = ref([
    { label: 'FACTURA', value: 'INVOICE' },
    { label: 'BVME PARA TRANSPORTE FERROVIARIO', value: 'RAILWAY_TICKET' },
    { label: 'RECIBO DE HONORARIOS PROFESIONALES', value: 'FEE_RECEIPT' },
    { label: 'BOLETA DE VENTA', value: 'SALE_TICKET' },
    { label: 'HOJA DE INGRESO', value: 'ENTRY_SHEET' },
    { label: 'BOLETO DE ESPECTÁCULO PÚBLICO', value: 'EVENT_TICKET' },
    { label: 'OTROS RECIBOS', value: 'OTHER_RECEIPTS' },
    { label: 'BOLETOS DE TRANSPORTE URBANO', value: 'URBAN_TRANSPORT_TICKET' },
    { label: 'OTROS', value: 'OTHERS' },
  ]);

  const isIGV = (report: any) => {
    return (
      report.documentType === 'INVOICE' ||
      report.documentType === 'RAILWAY_TICKET' ||
      report.documentType === 'FEE_RECEIPT'
    );
  };

  const handleVerifyRuc = async (report: any) => {
    report.isVerified = false;
    report.isSearch = false;
    await validate(report.ruc);
    report.isSearch = true;
  };

  const handleStatusChange = (value: string, report: any) => {
    console.log('Value: ', value);
    report.isVerified = false;
    if (value === report.ruc) {
      report.isVerified = true;
    }
  };

  const tableRef = ref();
  // const tableRefItems = ref();
  const view = ref('items');
  const item = ref({});
  const activeKey = ref('1');

  const newItems = ref([]);

  const {
    isLoading,
    cash,
    effective,
    pagination,
    filters,
    fetchEffective,
    states,
    updateStatus,
    updateStatusItem,
    save,
    error,
    validate,
    rucs,
  } = useEffective();

  const { getStatus } = useDepartures();

  const rowClassName = (record: any) => {
    return record.expenseReports.some((report: any) => report.status === 'INVALIDATED')
      ? 'bg-danger'
      : record.unitCost > 0 && record.expenseReports.length === 0
        ? 'bg-warning'
        : '';
  };

  const clearFilters = async () => {
    filters.value = {
      type: 'date',
      date_from: '',
      date_to: '',
      term: '',
    };

    await fetchEffective();
  };

  const columns = [
    {
      title: '# Recibo',
      dataIndex: 'receiptNumber',
      key: 'receiptNumber',
      align: 'center',
    },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'center',
      width: '150px',
      isSlot: true,
    },
    {
      title: 'Guía',
      dataIndex: 'guideCode',
      key: 'guideCode',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Fecha',
      dataIndex: 'startDate',
      key: 'startDate',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'Paquete',
      dataIndex: 'templateName',
      key: 'templateName',
      width: '250px',
    },
    {
      title: 'Servicio',
      dataIndex: 'serviceCode',
      key: 'serviceCode',
      align: 'center',
    },
    {
      title: 'Tipo',
      dataIndex: 'typeName',
      key: 'typeName',
      align: 'center',
    },
    {
      title: 'Duración',
      dataIndex: 'durationName',
      key: 'durationName',
      align: 'center',
    },
    {
      title: 'File OPE',
      dataIndex: 'opeFile',
      key: 'opeFile',
      align: 'center',
    },
    {
      title: 'CNT. Pax',
      dataIndex: 'paxCount',
      key: 'paxCount',
      align: 'center',
    },
    {
      title: 'Monto Inicial (PEN)',
      dataIndex: 'totalAmount',
      key: 'totalAmount',
      align: 'center',
    },
    {
      title: 'Monto Real (PEN)',
      dataIndex: 'realAmount',
      key: 'realAmount',
      align: 'center',
    },
    {
      title: 'Resumen',
      dataIndex: 'summary',
      key: 'summary',
      align: 'center',
      isSlot: true,
    },
  ];

  const columnsItems = [
    {
      title: 'FECHA',
      dataIndex: 'startDate',
      key: 'startDate',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'CATEGORÍA',
      dataIndex: 'categoryName',
      key: 'categoryName',
      align: 'center',
      isSlot: true,
      width: 100,
    },
    {
      title: 'SERVICIO',
      dataIndex: 'serviceName',
      key: 'serviceName',
      align: 'center',
      isSlot: true,
      width: 200,
    },
    {
      title: 'CNT. PROVEEDORES',
      dataIndex: 'providerCount',
      key: 'providerCount',
      align: 'center',
    },
    {
      title: 'COSTO UNITARIO',
      dataIndex: 'unitCost',
      key: 'unitCost',
      align: 'center',
    },
    {
      title: 'PAX',
      dataIndex: 'paxCount',
      key: 'paxCount',
      align: 'center',
    },
    {
      title: 'COSTO/PAQ',
      dataIndex: 'total',
      key: 'total',
      align: 'center',
    },
    {
      title: 'COSTO/REAL',
      dataIndex: 'realTotal',
      key: 'realTotal',
      align: 'center',
    },
    {
      title: 'DIFERENCIA',
      dataIndex: 'difference',
      key: 'difference',
      align: 'center',
    },
    {
      title: 'DOCUMENTOS',
      dataIndex: 'documents',
      key: 'documents',
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

  const tableOptionsItems = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const showModal = ref(false);

  onBeforeMount(async () => {
    await fetchEffective();
  });

  const openModal = debounce(async (_record: any) => {
    cash.value = JSON.parse(JSON.stringify(_record));
    item.value = {};
    view.value = 'items';
    showModal.value = true;
  }, 350);

  const handleCancel = () => {
    showModal.value = false;

    setTimeout(() => {
      cash.value = null;
      item.value = {};
    }, 10);
  };

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;
    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);
    await fetchEffective();
  };

  const getDocumentType = (type: string) => {
    return documentTypes.value.find((document) => document.value === type)?.label;
  };

  const showDocuments = (record: any) => {
    newItems.value = [];
    rucs.value = [];

    if (isProvider.value) {
      newItems.value =
        record?.expenseReports.map((report: any) => {
          return {
            ...report,
            issueDate: moment(report.issueDate).format('YYYY-MM-DD'),
          };
        }) ?? [];
    } else {
      record.expenseReports =
        record?.expenseReports.map((report: any) => {
          return {
            ...report,
            isSearch: false,
            issueDate: moment(report.issueDate).format('YYYY-MM-DD'),
          };
        }) ?? [];

      if (record.expenseReports.length === 0) {
        notification.warning({
          message: 'Advertencia',
          description: 'No se encontraron documentos',
        });
        return;
      }

      view.value = 'documents';
      item.value = record;
    }
  };

  const deleteNewItem = async (index: number) => {
    let quantityDeleted = 0;
    if (newItems.value[index]._id) {
      Modal.confirm({
        title: '¿Está seguro de eliminar este documento?',
        content: 'Este documento será eliminado permanentemente',
        okText: 'Sí',
        cancelText: 'No',
        onOk: async () => {
          quantityDeleted += 1;
          newItems.value.splice(index, 1);

          if (newItems.value.length === 0) {
            await handleSaveDocuments();
          }
        },
      });
    } else {
      newItems.value.splice(index, 1);
    }

    if (quantityDeleted > 0 && newItems.value.length === 0) {
      await handleSaveDocuments();
    }
  };

  const addNewItem = () => {
    newItems.value.push({
      documentType: documentType.value ?? '',
      issueDate: '',
      ruc: '',
      series: '',
      documentNumber: '',
      subtotal: 0,
      igv: 0,
      total: 0,
      images: [],
      status: 'PENDING',
    });
  };

  const handleBack = () => {
    view.value = 'items';
    item.value = null;
  };

  const handleSaveDocuments = async () => {
    await save(cash.value._id, item.value.serviceId, newItems.value);

    if (!error.value) {
      const itemView = cash.value.items.find(
        (itemX: any) => itemX.serviceId === item.value.serviceId
      );
      showDocuments(itemView);

      await fetchEffective();
      notification.success({
        message: 'Documentos guardados correctamente',
      });
    } else {
      notification.error({
        message: error.value || 'Error al guardar documentos',
      });
    }
  };

  const handleValidate = async (type: string, report: any) => {
    if (type === 'report') {
      await updateStatus(item.value.serviceId, report._id, 'validate');

      if (!error.value) {
        notification.success({
          message: 'Documento validado correctamente',
        });
        report.status = 'VALIDATED';
        await fetchEffective();
      }
    }

    if (type === 'item') {
      await updateStatusItem({ status: 'VALIDATED' });

      if (!error.value) {
        notification.success({
          message: 'Requerimiento validado correctamente',
        });
        cash.value.status = 'VALIDATED';
        await fetchEffective();
      }
    }

    if (error.value) {
      notification.error({
        message: error.value || 'Error al validar',
      });
    }
  };

  const handleInvalidate = async (type: string, report: any) => {
    if (type === 'report') {
      await updateStatus(item.value.serviceId, report._id, 'invalidate');

      if (!error.value) {
        notification.success({
          message: 'Documento invalidado correctamente',
        });
        report.status = 'INVALIDATED';
        await fetchEffective();
      }
    }

    if (type === 'item') {
      await updateStatusItem({ status: 'REJECTED' });

      if (!error.value) {
        notification.success({
          message: 'Requerimiento invalidado correctamente',
        });
        cash.value.status = 'REJECTED';
        await fetchEffective();
      }
    }

    if (error.value) {
      notification.error({
        message: error.value || 'Error al invalidar',
      });
    }
  };

  const updateImages = (files: any, report: any) => {
    report.images = [...report.images, ...files.map((file: any) => file.link)];
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';

  .document-accordion-container {
    max-width: 800px; /* Ancho máximo para que no se vea demasiado estirado */
    margin: 20px auto;
    border: 1px solid #d9d9d9;
    border-radius: 4px;
  }

  /* Estilos para el header personalizado del acordeón */
  .custom-header {
    display: flex;
    align-items: center;
  }

  .header-title {
    font-weight: 600;
    font-size: 16px;
    margin-left: 8px;
    text-transform: uppercase;
  }

  /* Espaciado interno del panel */
  .panel-content {
    padding: 16px 8px;
  }

  .upload-hint {
    margin-top: 12px;
    color: #8c8c8c;
    font-size: 12px;
  }

  /* Estilos para los botones de acción inferiores */
  .action-buttons {
    display: flex;
    gap: 16px; /* Espacio entre botones */
    margin-top: 32px;
  }

  /* Colores personalizados para los botones para coincidir con la imagen */
  .btn-validate {
    /* Color verde oscuro personalizado */
    background-color: #387c2b;
    border-color: #387c2b;
  }
  .btn-validate:hover,
  .btn-validate:focus {
    background-color: #459635;
    border-color: #459635;
  }

  .btn-invalidate {
    /* Color rojo oscuro personalizado (ant-design ya provee uno bueno, pero podemos forzarlo) */
    background-color: #a81f24;
    border-color: #a81f24;
  }
  .btn-invalidate:hover,
  .btn-invalidate:focus {
    background-color: #c72a30;
    border-color: #c72a30;
  }

  /* Ajuste responsivo para botones en pantallas muy pequeñas */
  @media (max-width: 576px) {
    .action-buttons {
      flex-direction: column;
    }
  }
</style>
