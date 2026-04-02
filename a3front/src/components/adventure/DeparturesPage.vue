<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Salidas</h1>
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
                  <a-form-item label="Tipo de Búsqueda:" name="value" class="mb-0">
                    <a-select
                      v-model:value="filters.type"
                      placeholder="Seleccione un tipo"
                      :show-search="false"
                      :allow-clear="false"
                      :options="types"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <template v-if="filters.type === 'date'">
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
                <template v-if="filters.type === 'file'">
                  <a-col :span="3">
                    <a-form-item label="File:" name="value" class="mb-0">
                      <a-input
                        autocomplete="off"
                        type="number"
                        v-model:value="filters.file"
                        placeholder="Ingrese un file.."
                      />
                    </a-form-item>
                  </a-col>
                </template>
                <template v-else>
                  <a-col :span="5">
                    <a-form-item label="Término de Búsqueda:" name="value" class="mb-0">
                      <a-input
                        autocomplete="off"
                        v-model:value="filters.term"
                        placeholder="Ingrese término.."
                      />
                    </a-form-item>
                  </a-col>
                </template>
                <a-col>
                  <a-button type="primary" :disabled="isLoading" @click="handleSearchDepartures">
                    <SearchOutlined />
                  </a-button>
                </a-col>
                <a-col>
                  <a-button type="dashed" :disabled="isLoading" @click="handleClearFilters">
                    <SyncOutlined />
                  </a-button>
                </a-col>
                <a-col>
                  <a-tooltip title="Exportar a un Excel">
                    <a-button
                      type="primary"
                      class="bg-success"
                      :disabled="isLoading"
                      @click="exportDepartures"
                    >
                      <FileExcelOutlined />
                    </a-button>
                  </a-tooltip>
                </a-col>
              </a-row>
            </a-form>
          </a-col>
          <a-col>
            <a-button type="primary" size="large" :disabled="isLoading" @click="handleAdd">
              <PlusSquareOutlined /> Crear Salida
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- Tabla -->
    <backend-table-component
      ref="tableRef"
      :items="departures"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      :custom-row="customRow"
      @change="handleChange"
    >
      <template #state="{ record }">
        <p class="text-uppercase text-500 mb-0">
          <template v-if="record.isClosed">
            <span class="d-block"> <CheckCircleOutlined /> Cerrado </span>
          </template>
          <template v-else>
            <template
              v-if="record.isCompleted && !record.pendingProgramming && !record.pendingRequirement"
            >
              <span class="d-block"> <CheckCircleOutlined /> Listo para cerrar </span>
            </template>
            <template v-else>
              <template v-if="!record.isCompleted">
                <span class="d-block"> <ExclamationCircleOutlined /> Salida no culmina </span>
              </template>
              <template v-if="record.pendingProgramming">
                <span class="d-block"> <ExclamationCircleOutlined /> Pendiente Programación </span>
              </template>
              <template v-if="record.pendingRequirement">
                <span class="d-block">
                  <ExclamationCircleOutlined /> Pendiente Requerimientos Efectivo
                </span>
              </template>
            </template>
          </template>
        </p>
      </template>
      <template #actions="{ record }">
        <a-space>
          <a-dropdown>
            <template #overlay>
              <a-menu>
                <a-menu-item @click="handleFile(record)"> <ExportOutlined /> FILE</a-menu-item>
                <a-menu-item @click="handleProgramming(record)">
                  <ClockCircleOutlined /> Programación</a-menu-item
                >
                <a-menu-item @click="openModal('files', record)">
                  <PlusOutlined /> Asignar FILES</a-menu-item
                >
                <a-menu-item @click="openModal('guides', record)">
                  <UserAddOutlined /> Asignar GUI</a-menu-item
                >
                <a-menu-item
                  @click="openModal('cash', record)"
                  :disabled="record.commercialFiles.length === 0"
                >
                  <DollarOutlined /> Requerimiento de Efectivo</a-menu-item
                >
                <a-menu-item @click="openModal('service', record)">
                  <PlusOutlined /> Agregar servicio adicional</a-menu-item
                >
                <a-menu-item @click="openModal('services', record)">
                  <EyeOutlined /> Ver servicios adicionales</a-menu-item
                >
                <a-menu-item
                  @click="openModal('equilibrio', record)"
                  :disabled="record.template.type === 'private'"
                >
                  <TableOutlined /> Punto de equilibrio</a-menu-item
                >
                <a-menu-item @click="handleDelete(record)">
                  <DeleteOutlined /> Eliminar salida
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
    v-model:visible="allModalStates.equilibrio.value"
    title="PUNTO DE EQUILIBRIO"
    :confirm-loading="isLoading"
    @ok="handleOkBreakPoint"
    @cancel="handleCancel"
    okText="GUARDAR"
    cancelText="CANCELAR"
    width="400px"
  >
    <backend-table-component
      :items="breakpoints"
      :columns="columnBreakpoints"
      :options="tableOptionBreakpoints"
      size="small"
      :custom-row="customRowBreak"
    >
      <template #pax="{ record }">
        <span class="text-500" style="font-size: 12px">{{ record.pax }}</span>
      </template>
      <template #cost="{ record }">
        <span class="text-500" style="font-size: 12px">{{ record.cost }}</span>
      </template>
      <template #actions="{ index }">
        <a-tooltip placement="right" title="Marcar / Desmarcar como punto de equilibrio">
          <template v-if="breakpoints[index].selected">
            <a-button type="dashed" danger @click="toggleBreakpoint(index)">
              <DeleteOutlined />
            </a-button>
          </template>
          <template v-if="!breakpoints.some((bp: any) => bp.selected)">
            <a-button type="dashed" danger @click="toggleBreakpoint(index)">
              <PlusOutlined />
            </a-button>
          </template>
        </a-tooltip>
      </template>
    </backend-table-component>
  </a-modal>

  <!-- Modal para agregar/actualizar categoría -->
  <a-modal
    v-model:visible="showModal"
    title="CREAR SALIDA"
    :confirm-loading="isLoading"
    @ok="handleOk"
    @cancel="handleCancel"
    okText="GUARDAR"
    cancelText="CANCELAR"
    width="400px"
  >
    <a-form :model="departure" :rules="rules" layout="vertical" ref="formRef">
      <a-form-item label="Escoger paquete" name="template_id">
        <a-select
          v-model:value="departure.template_id"
          placeholder="Escoger un paquete"
          size="large"
          :show-search="true"
          :allow-clear="false"
          :filter-option="false"
          @search="handleSearchTemplate"
          :options="templates"
        />
      </a-form-item>
      <a-form-item label="Fecha" name="date">
        <a-date-picker
          class="w-100"
          v-model:value="departure.date"
          placeholder="Seleccione una fecha"
          size="large"
          format="DD/MM/YYYY"
          value-format="YYYY-MM-DD"
        />
      </a-form-item>
    </a-form>
  </a-modal>

  <a-modal
    v-model:visible="allModalStates.files.value"
    title="ASIGNAR FILES"
    :confirm-loading="isLoading"
    @ok="handleOkFiles"
    @cancel="handleCancel"
    okText="PROCESAR"
    cancelText="CANCELAR"
    width="700px"
  >
    <a-form :model="departure" layout="vertical">
      <a-form-item label="FILE:" name="file">
        <a-input
          v-model:value="searchFile"
          placeholder="Ingrese FILE y presione ENTER.."
          size="large"
          autocomplete="off"
          @change="handleSearchFile"
        />
      </a-form-item>
    </a-form>

    <p class="mt-3 mb-0 text-500" style="font-size: 12px">PAXS PARA ASOCIAR A LA SALIDA:</p>

    <backend-table-component
      :items="paxsSelected"
      :columns="columnsFilesSelected"
      :options="tableOptionFiles"
      size="small"
      :actionHeader="paxsSelected.length > 0 ? handleAddAllPaxs : null"
      :actionTitle="paxsSelected.length > 0 ? 'Asignar todos los pasajeros del FILE' : ''"
    >
      <template #actions="{ index }">
        <a-tooltip placement="right" title="Marcar / Desmarcar PAX">
          <a-button type="dashed" danger @click="togglePax(index)">
            <PlusOutlined />
          </a-button>
        </a-tooltip>
      </template>
    </backend-table-component>

    <p class="mt-3 mb-0 text-500" style="font-size: 12px">PAXS EN SALIDA:</p>

    <backend-table-component
      :items="paxs"
      :columns="columnsFilesPaxs"
      :options="tableOptionFiles"
      size="small"
    >
      <template #actions="{ index }">
        <a-tooltip placement="right" title="Eliminar PAX">
          <a-button type="dashed" danger @click="handleRemovePax(index)">
            <DeleteOutlined />
          </a-button>
        </a-tooltip>
      </template>
    </backend-table-component>
  </a-modal>

  <a-modal
    v-model:visible="allModalStates.guides.value"
    title="ASIGNAR GUÍA"
    :confirm-loading="isLoading"
    @ok="handleOkGuides"
    @cancel="handleCancel"
    okText="PROCESAR"
    cancelText="CANCELAR"
    width="500px"
  >
    <a-form layout="vertical">
      <a-form-item label="Guía:" name="guide">
        <a-select
          show-search
          placeholder="Código o Nombre de guía"
          size="large"
          style="width: 100%"
          :filter-option="false"
          :not-found-content="null"
          @search="onSearchInternal"
          @select="onSelectInternal"
        >
          <a-select-option
            v-for="(record, index) in guides"
            :key="record.codigo"
            :value="record.codigo"
            :data-index="index"
          >
            <div style="display: flex; justify-content: space-between; align-items: center">
              <span>
                <b>{{ record.codigo }}</b> - {{ record.razon }}
              </span>
              <a-tag color="red">{{ record.type }}</a-tag>
            </div>
          </a-select-option>
        </a-select>
      </a-form-item>

      <a-alert type="warning" class="mb-0" show-icon v-if="departure.guideCode">
        <template #icon>
          <CheckCircleOutlined />
        </template>
        <template #description>
          <p class="mb-0">
            Guía seleccionado: <b>{{ departure.guideCode }}</b>
          </p>
        </template>
      </a-alert>
    </a-form>
  </a-modal>

  <a-modal
    v-model:visible="allModalStates.cash.value"
    :ok-button-props="{ hidden: true }"
    title="REQUERIMIENTO DE EFECTIVO"
    :confirm-loading="isLoading"
    @cancel="handleCancel"
    cancelText="CERRAR"
    width="900px"
  >
    <p class="text-center mb-0">
      <b>{{ departure.templateName ?? '' }}</b>
    </p>
    <p class="text-center">
      <small>{{ departure.dateStart }} - {{ departure.dateEnd }}</small>
    </p>
    <a-alert type="warning" v-if="departure.showAlert" show-icon>
      <template #icon>
        <ExclamationCircleOutlined />
      </template>
      <template #description>
        <p class="mb-0">
          <b>Para realizar el requerimiento de efectivo es necesario cumplir con lo siguiente:</b>
        </p>
        <p class="mb-0">
          <template v-if="!departure.guideCode">
            Asignar código de Guía a la salida para proseguir. Haber realizado el pedido de
            ENTRADAS.
          </template>
        </p>
      </template>
    </a-alert>

    <a-collapse :bordered="false" v-if="cash?.items.length > 0 || cashExtra?.items.length > 0">
      <template v-if="cash?.items && cash?.items.length > 0">
        <a-collapse-panel>
          <template #header>
            <template v-if="cash.status === 'NOT_REQUIRED'">
              <p class="mb-0">SERVICIOS SIN REQUERIR:</p>
            </template>
            <template v-else>
              <p class="mb-0">REC. {{ cash.receiptNumber }} - {{ getStatus(cash.status) }}:</p>
            </template>
          </template>

          <template v-if="cash?.statusHistory && cash.statusHistory.length > 0">
            <timeline-cash @resendNotification="handleResendNotification" :cash="cash" />
          </template>
          <backend-table-component
            :items="cash.items"
            :columns="columnCash"
            :options="tableOptionCash"
            size="small"
          />
          <a-row type="flex" justify="end" style="gap: 10px" class="m-2">
            <a-col> TOTAL: </a-col>
            <a-col>
              <b>{{ cash?.totals?.totalAmount ?? '-' }} {{ cash?.totals?.currency ?? '-' }}</b>
            </a-col>
          </a-row>
          <a-row
            type="flex"
            justify="center"
            v-if="!cash?.status || cash.status === 'NOT_REQUIRED'"
          >
            <a-col>
              <a-button
                type="primary"
                class="mt-3"
                :disabled="isLoading || !departure.guideCode"
                @click="handleProcessCash('')"
              >
                Realizar Requerimiento
              </a-button>
            </a-col>
          </a-row>
          <a-row type="flex" justify="center" v-if="cash.status === 'PETTY_CASH'">
            <a-col>
              <a-button
                type="primary"
                class="mt-3"
                :disabled="isLoading || !departure.guideCode"
                @click="handleUpdateCash(cash._id, 'DELIVERED')"
              >
                Registrar entrega de efectivo ({{ departure.guideCode }})
              </a-button>
            </a-col>
          </a-row>
        </a-collapse-panel>
      </template>
      <template v-if="cashExtra?.items && cashExtra?.items.length > 0">
        <a-collapse-panel>
          <template #header>
            <template v-if="cash.status === 'NOT_REQUIRED'">
              <p class="mb-0">SERVICIOS EXTRA SIN REQUERIR:</p>
            </template>
            <template v-else>
              <p class="mb-0">REC. {{ cash.receiptNumber }} - {{ getStatus(cash.status) }}:</p>
            </template>
          </template>

          <template v-if="cashExtra?.statusHistory && cashExtra.statusHistory.length > 0">
            <timeline-cash @resendNotification="handleResendNotification" :cash="cashExtra" />
          </template>
          <backend-table-component
            :items="cashExtra.items"
            :columns="columnCash"
            :options="tableOptionCash"
            size="small"
          />
          <a-row type="flex" justify="end" style="gap: 10px" class="m-2">
            <a-col> TOTAL: </a-col>
            <a-col>
              <b>{{ cashExtra?.totals?.total ?? '-' }} {{ cashExtra?.totals?.currency ?? '-' }}</b>
            </a-col>
          </a-row>
          <a-row
            type="flex"
            justify="center"
            v-if="!cashExtra?.status || cashExtra.status === 'NOT_REQUIRED'"
          >
            <a-col>
              <a-button
                type="primary"
                class="mt-3"
                :disabled="isLoading || !departure.guideCode"
                @click="handleProcessCash('extra')"
              >
                Realizar Requerimiento
              </a-button>
            </a-col>
          </a-row>

          <a-row type="flex" justify="center" v-if="cashExtra.status === 'PETTY_CASH'">
            <a-col>
              <a-button
                type="primary"
                class="mt-3"
                :disabled="isLoading || !departure.guideCode"
                @click="handleUpdateCash(cashExtra._id, 'DELIVERED')"
              >
                Registrar entrega de efectivo ({{ departure.guideCode }})
              </a-button>
            </a-col>
          </a-row>
        </a-collapse-panel>
      </template>
    </a-collapse>
  </a-modal>

  <template v-if="allModalStates.service.value">
    <add-service-modal
      :visible="allModalStates.service.value"
      :locked="true"
      @handleOk="handleOkService"
      @handleCancel="handleCancel"
    />
  </template>

  <a-modal
    :ok-button-props="{ hidden: true }"
    v-model:visible="allModalStates.services.value"
    title="SERVICIOS ADICIONALES"
    :confirm-loading="isLoading"
    @cancel="handleCancel"
    cancelText="CERRAR"
    width="1200px"
  >
    <!-- a-alert type="warning">
      <template #description>
        <p class="mb-0">
          <b>Para realizar el requerimiento de efectivo es necesario cumplir con lo siguiente:</b>
        </p>
        <p class="mb-0">
          Asignar código de GUI a la salida para proseguir. Haber realizado el pedido de ENTRADAS
          (Ir al módulo entradas).
        </p>
      </template>
    </a-alert -->

    <backend-table-component
      :items="services"
      :columns="columnServices"
      :options="tableOptionServices"
      size="small"
    />
  </a-modal>
</template>

<script setup lang="ts">
  import moment from 'moment';
  import { ref, onBeforeMount, h, createVNode, computed } from 'vue';
  import { useDepartures, useTemplates, useExtraServices } from '@/composables/adventure';
  import { createPaxAdapter } from '@/stores/adventure/adapters';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import AddServiceModal from '@/components/adventure/components/AddServiceModal.vue';
  import TimelineCash from '@/components/adventure/components/TimelineCash.vue';
  import {
    CheckCircleOutlined,
    ClockCircleOutlined,
    DeleteOutlined,
    DollarOutlined,
    ExclamationCircleOutlined,
    ExportOutlined,
    EyeOutlined,
    FileExcelOutlined,
    PlusOutlined,
    PlusSquareOutlined,
    SearchOutlined,
    SettingFilled,
    SyncOutlined,
    TableOutlined,
    UserAddOutlined,
  } from '@ant-design/icons-vue';

  import { Tooltip as ATooltip, Tag as ATag, Modal, notification } from 'ant-design-vue';

  import { useRouter } from 'vue-router';
  import { debounce } from 'lodash';
  const router = useRouter();

  const dateFormat = 'DD/MM/YYYY';
  const tableRef = ref();
  const searchFile = ref('');
  const searchGuide = ref('');

  const {
    isLoading,
    departure,
    departures,
    pagination,
    filters,
    types,
    fetchDepartures,
    exportDepartures,
    saveDeparture,
    // deleteDeparture,
    deactivateDeparture,
    error,
    status,
    searchPaxsByFile,
    paxsByFile,
    searchGuides,
    guides,
    savePaxsToDeparture,
    deletePaxToDeparture,
    saveGuide,
    cash,
    cashExtra,
    processGenerateCash,
    searchCash,
    searchCashExtra,
    fetchDepartureServices,
    services,
    resendNotification,
    updateCash,
    getStatus,
    clearFilters,
  } = useDepartures();

  const { saveDepartureExtraService, extraService } = useExtraServices();
  const {
    template,
    pagination: paginationTemplates,
    filters: filtersTemplate,
    templates,
    fetchTemplates,
    breakpoints,
    fetchTemplateBreakpoints,
    saveTemplateBreakpoint,
  } = useTemplates();

  const paxs = ref([]);

  const handleSearchFile = debounce(async () => {
    if (searchFile.value) {
      await searchPaxsByFile(searchFile.value);
    }
  }, 350);

  const paxsSelected = computed(() => {
    const excludedKeys = new Set(paxs.value.map((np: any) => `${np.file}-${np.document}-${np.id}`));

    return paxsByFile.value.filter((pax: any) => {
      const key = `${pax.file}-${pax.document}-${pax.id}`;
      return !excludedKeys.has(key);
    });
  });

  const onSearchInternal = async (val: string) => {
    searchGuide.value = val;
    await handleSearchGuides();
  };

  const onSelectInternal = (_: string, option: any) => {
    const code = option['value'];

    if (code !== undefined) {
      handleAssignGuide(code);
    }
  };

  const handleSearchGuides = debounce(async () => {
    if (searchGuide.value) {
      await searchGuides(searchGuide.value, 'GUI');
    }
  }, 350);

  const togglePax = (index: number) => {
    paxsSelected.value = paxsSelected.value.map((pax: any, i: number) => {
      if (i === index) {
        pax.nrofile = searchFile.value;
        pax.selected = true;
      }
      return pax;
    });

    paxs.value = sortPaxs(
      [...paxs.value, ...paxsSelected.value.filter((p: any) => p.selected)].map((pax: any) =>
        createPaxAdapter(pax.file, pax)
      )
    );
  };

  const handleAssignGuide = (code: string) => {
    const guide = guides.value.find((guide: any) => guide.codigo === code);
    guide.selected = !guide.selected;
    departure.value.guideCode = guide.codigo;
  };

  const handleSearchDepartures = async () => {
    pagination.value.current = 1;
    await fetchDepartures();
  };

  const handleClearFilters = async () => {
    clearFilters();
    await fetchDepartures();
  };

  const customRow = (record: any) => {
    return {
      style: {
        'background-color': record.rowColor ? record.rowColor : 'inherit',
      },
    };
  };

  const customRowBreak = (record: any) => {
    return {
      style: {
        'background-color': record.selected ? '#897A7A' : 'inherit',
      },
    };
  };

  const columns = [
    {
      title: 'Fecha',
      dataIndex: 'dateStart',
      key: 'dateStart',
      align: 'center',
    },
    {
      title: 'Paquete',
      dataIndex: 'templateName',
      key: 'templateName',
      width: 200,
      customRender: function (_value: any) {
        return `<span><b>${_value}</b></span>`;
      },
    },
    {
      title: 'Codsvs',
      dataIndex: 'templateCode',
      key: 'templateCode',
      align: 'center',
      customRender: function (_value: any) {
        return `<span><b>${_value}</b></span>`;
      },
    },
    {
      title: 'Tipo',
      dataIndex: 'newType',
      key: 'newType',
      align: 'center',
    },
    {
      title: 'Duración',
      dataIndex: 'duration',
      key: 'duration',
      align: 'center',
    },
    {
      title: 'File OPE',
      dataIndex: 'opeFile',
      key: 'opeFile',
      align: 'center',
    },
    {
      title: 'Files Comerciales',
      dataIndex: 'commercialFiles',
      key: 'commercialFiles',
      align: 'center',
      width: 200,
      isComponent: true,
      customRender: (data: any, record: any) => {
        if (data && data.length > 0) {
          const vNodes = data.map((value: any, index: number) => {
            const paxsLength = value?.paxs?.length || 0;

            return h(
              ATooltip,
              {
                title: [
                  h(
                    'span',
                    `${paxsLength} PAX${paxsLength > 1 ? 'S' : ''} ASOCIADO${paxsLength > 1 ? 'S' : ''} AL FILE`
                  ),
                ],
                placement: 'top',
              },
              [
                h(
                  ATag,
                  {
                    color: 'red',
                    class: `cursor-pointer ${index < data.length - 2 ? 'mb-2' : ''}`,
                    onclick: () => {
                      paxs.value = (value?.paxs ?? []).map((pax: any) =>
                        createPaxAdapter(value.file.number, pax)
                      );
                      openModal('files', record);
                    },
                  },
                  value.file.number || '-'
                ),
              ]
            );
          });
          return h('div', {}, vNodes);
        }
        return h('div', null, '-');
      },
    },
    {
      title: 'CNT. Pax',
      dataIndex: 'paxs',
      key: 'paxs',
      align: 'center',
    },
    {
      title: 'Guía',
      dataIndex: 'guideCode',
      key: 'guideCode',
      align: 'center',
      isComponent: true,
      customRender: (value: any, record: any) => {
        if (value) {
          const vNodes = h(
            ATag,
            {
              color: 'red',
              class: 'cursor-pointer',
              onclick: () => {
                openModal('guides', record);
              },
            },
            value || '-'
          );

          return h('div', {}, vNodes);
        }
        return h('div', {}, '-');
      },
    },
    {
      title: 'Req. Efectivo',
      dataIndex: 'cashRequirements',
      key: 'cashRequirements',
      align: 'center',
      customRender: (value: any) => {
        let html = '';
        if (value.length > 0) {
          value.map((item: any) => {
            const state = status.value.find((s: any) => s.value === item.status);
            html += `<b class="d-block">REC. ${item?.receiptNumber || '-'} &bull; ${state?.label}</b>`;
            const histories = item.statusHistory.filter(
              (history: any) => history.status === item.status
            );
            for (const history of histories) {
              html += `<span class="d-block text-uppercase">${history.user} | ${moment(history.timestamp).format('DD/MM/YYYY HH:mm')}</span>`;
            }
          });
          return html;
        }
        return 'No existen requerimientos';
      },
    },
    {
      title: 'Estado FILE',
      dataIndex: 'state',
      key: 'state',
      align: 'center',
      isSlot: true,
    },
  ];

  const getColumnsWithGrouping = (data: any) => [
    {
      title: 'File',
      dataIndex: 'file',
      key: 'file',
      align: 'center',
      customCell: (_: any, index: number) => {
        if (!data || data.length === 0) return {};
        const file = String(data[index].file);
        if (index > 0 && String(data[index - 1].file) === file) {
          return { rowSpan: 0 };
        }
        let count = 1;
        for (let i = index + 1; i < data.length; i++) {
          if (String(data[i].file) === file) {
            count++;
          } else {
            break;
          }
        }
        return { rowSpan: count };
      },
    },
    {
      title: 'ID',
      dataIndex: 'id',
      key: 'id',
      align: 'center',
    },
    {
      title: 'Nombre',
      dataIndex: 'name',
      key: 'name',
    },
    {
      title: 'Documento',
      dataIndex: 'document',
      key: 'document',
      align: 'center',
    },
    {
      title: 'Sexo',
      dataIndex: 'genre',
      key: 'genre',
      align: 'center',
    },
    {
      title: () => h(SettingFilled),
      dataIndex: 'actions',
      key: 'actions',
      align: 'center',
    },
  ];

  const columnsFilesPaxs = computed(() => getColumnsWithGrouping(paxs.value));
  const columnsFilesSelected = computed(() => getColumnsWithGrouping(paxsSelected.value));

  const columnCash = [
    {
      title: 'Fecha',
      dataIndex: 'dateStart',
      key: 'dateStart',
      align: 'center',
      customRender: function (value: any) {
        return `<span>${moment(value, 'YYYY-MM-DD').format('DD/MM/YYYY')}</span>`;
      },
    },
    {
      title: 'Categoría',
      dataIndex: 'categoryName',
      key: 'categoryName',
      width: '100px',
      align: 'center',
      customRender: function (value: any) {
        return `<span>${value}</span>`;
      },
    },
    {
      title: 'Servicio',
      dataIndex: 'serviceName',
      key: 'serviceName',
      align: 'center',
      width: '250px',
      customRender: function (value: any) {
        return `<span>${value}</span>`;
      },
    },
    {
      title: 'Proveedor(es)',
      dataIndex: 'providerCount',
      key: 'providerCount',
      align: 'center',
    },
    {
      title: 'Costo Unitario',
      dataIndex: 'unitCost',
      key: 'unitCost',
      align: 'center',
      customRender: function (value: any, record: any) {
        let content = `<span class="d-block">${value} ${record?.currency}</span>`;
        if (record?.type === 'range') {
          if (record?.pricing?.length === 1) {
            content += `<small><i class="d-block">Hasta ${record.pricing[0].pax ?? ''} pax.</i></small>`;
          } else {
            content += `<small><i class="d-block">Hasta ${record.paxCount} pax.</i></small>`;
          }
        }
        return content;
      },
    },
    {
      title: 'Paxs',
      dataIndex: 'paxCount',
      key: 'paxCount',
      align: 'center',
    },
    {
      title: 'Costo PAQ',
      dataIndex: 'total',
      key: 'total',
      align: 'center',
      customRender: function (value: any, record: any) {
        return `<span>${value} ${record?.currency}</span>`;
      },
    },
  ];

  const columnServices = [
    {
      title: 'Fecha',
      dataIndex: 'startDate',
      key: 'startDate',
    },
    {
      title: 'Categoría',
      dataIndex: 'categoryName',
      key: 'type',
    },
    {
      title: 'Servicio',
      dataIndex: 'name',
      key: 'name',
      customRender: function (value: any) {
        return `<small><b>${value}</b></small>`;
      },
    },
    {
      title: 'CNT. Proveedores',
      dataIndex: 'providerCount',
      key: 'providerCount',
      align: 'center',
    },
    {
      title: 'Costo Unitario (PEN)',
      dataIndex: 'priceUnit',
      key: 'priceUnit',
      align: 'center',
    },
    {
      title: 'Cantidad (PAX)',
      dataIndex: 'pax',
      key: 'pax',
      align: 'center',
    },
    {
      title: 'Costo PAQ (PEN)',
      dataIndex: 'priceTotal',
      key: 'priceTotal',
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: true,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  const tableOptionFiles = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const tableOptionCash = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const tableOptionServices = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const rules = {
    template_id: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    date: [{ required: true, message: 'Requerido', trigger: 'blur' }],
  };

  const showModal = ref(false);

  const allModalStates = {
    files: ref(false),
    guides: ref(false),
    cash: ref(false),
    service: ref(false),
    services: ref(false),
    equilibrio: ref(false),
  };

  const formRef = ref();

  const handleSearchTemplate = debounce(async (filter: string) => {
    if ((templates.value === 0 && !filter) || filter) {
      filtersTemplate.value = {
        term: filter,
      };
      await fetchTemplates();
    }
  }, 350);

  onBeforeMount(async () => {
    paginationTemplates.value.current = 1;
    paginationTemplates.value.pageSize = 500;

    await fetchTemplates();
    await fetchDepartures();
  });

  const handleOk = async () => {
    await saveDeparture();

    if (!error.value) {
      paginationTemplates.value.current = 1;
      tableRef.value.setPage(1);

      await fetchDepartures();
      handleCancel();
    } else {
      notification.error({
        message: 'Error',
        description: error.value ?? 'Ocurrió un error al guardar la salida.',
      });
    }
  };

  const handleOkService = async (_extraService: any) => {
    try {
      await formRef.value.validate();
      extraService.value = JSON.parse(JSON.stringify(_extraService));

      if (!error.value) {
        await saveDepartureExtraService(departure.value._id);

        if (!error.value) {
          await fetchDepartures();
          handleCancel();
        }
      }
    } catch (err) {
      console.log(err);
    }
  };

  const handleOkFiles = async () => {
    try {
      if (paxs.value.length > 0 && searchFile.value) {
        const parsedData = Object.values(
          paxs.value.reduce((acc: any, item: any) => {
            const fileKey = item.file.toString();

            // Si el grupo (file) no existe, lo creamos
            if (!acc[fileKey]) {
              acc[fileKey] = {
                file: fileKey,
                paxs: [],
              };
            }

            // Agregamos el pasajero formateado al array paxs
            acc[fileKey].paxs.push({
              id: item.id,
              fullName: item.fullName,
              documentNumber: item.documentNumber,
              sex: item.sex,
            });

            return acc;
          }, {})
        );

        await savePaxsToDeparture(parsedData);
        if (!error.value) {
          await fetchDepartures();
          handleCancel();
        } else {
          notification.error({
            message: 'Error',
            description: error.value,
          });

          openModal('files', departure.value);
        }
      } else {
        handleCancel();
      }
    } catch (error) {
      console.log(error);
    }
  };

  const handleOkGuides = async () => {
    try {
      if (!searchGuide.value && guides.value.length === 0) {
        handleCancel();
        return;
      } else {
        if (guides.value.length > 0) {
          await saveGuide();

          if (!error.value) {
            await fetchDepartures();
            notification.success({
              message: 'Éxito',
              description: 'El guía se registró correctamente',
            });
            handleCancel();
          } else {
            notification.error({
              message: 'Error',
              description: error.value,
            });
          }
        } else {
          notification.error({
            message: 'Error',
            description: 'Debe seleccionar un guia para continuar',
          });
        }
      }
    } catch (error) {
      console.log(error);
    }
  };

  const columnBreakpoints = [
    {
      title: 'PAX',
      dataIndex: 'pax',
      key: 'pax',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'PRECIO (USD)',
      dataIndex: 'cost',
      key: 'cost',
      align: 'center',
      isSlot: true,
    },
  ];

  const toggleBreakpoint = (index: number) => {
    breakpoints.value[index].selected = !breakpoints.value[index].selected;
  };

  const handleOkBreakPoint = async () => {
    try {
      const breakpoint = breakpoints.value.find((breakpoint: any) => breakpoint.selected);

      if (breakpoint) {
        await saveTemplateBreakpoint(breakpoint);
        if (!error.value) {
          await fetchDepartures();
          handleCancel();

          notification.success({
            message: 'Éxito',
            description: 'Punto de equilibrio guardado correctamente',
          });
        } else {
          notification.error({
            message: 'Error',
            description: error.value ?? 'Ocurrió un error al guardar el punto de equilibrio',
          });
        }
      } else {
        notification.error({
          message: 'Error',
          description: 'Debe seleccionar un punto de equilibrio',
        });
      }
    } catch (error) {
      console.error('Error al validar el formulario:', error);
    }
  };

  const tableOptionBreakpoints = {
    showActions: true,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const sortPaxs = (paxs: any[]) => {
    return paxs.sort((a: any, b: any) => {
      const fileSort = String(a.file).localeCompare(String(b.file));
      return fileSort || a.id - b.id;
    });
  };

  const openModal = debounce(async (_modal: string, _record: any) => {
    departure.value = JSON.parse(JSON.stringify(_record));

    if (_modal === 'files') {
      searchFile.value = '';
      paxsByFile.value = [];
      const adaptedFiles = _record.commercialFiles.flatMap((file: any) =>
        file.paxs.map((pax: any) => createPaxAdapter(file.file.number, pax))
      );

      paxs.value = sortPaxs(adaptedFiles);
    }

    if (_modal === 'cash') {
      await Promise.all([searchCash(), searchCashExtra()]);

      if (!departure.value.guideCode) {
        departure.value.showAlert = true;
      }

      if (cash.value.items.length === 0 && cashExtra.value.items.length === 0) {
        notification.error({
          message: 'Error',
          description: 'No se encontró requerimientos de efectivo disponible',
        });
        return false;
      }
    }

    if (_modal === 'guides') {
      searchGuide.value = '';
      guides.value = [];
    }

    if (_modal === 'services') {
      await fetchDepartureServices(departure.value._id);
    }

    if (_modal === 'equilibrio') {
      template.value = JSON.parse(JSON.stringify(_record.template));
      await fetchTemplateBreakpoints();
    }

    if (allModalStates[_modal] && allModalStates[_modal].value !== undefined) {
      allModalStates[_modal].value = true;
    } else {
      console.warn(`Modal con clave ${key} no encontrado.`);
    }
  }, 350);

  const handleCancel = () => {
    showModal.value = false;
    Object.keys(allModalStates).forEach((key) => {
      allModalStates[key].value = false;
    });
  };

  const handleAdd = () => {
    departure.value = {};
    showModal.value = true;
  };

  /*
  const handleEdit = (_departure: any) => {
    departure.value = JSON.parse(JSON.stringify(_departure));
    showModal.value = true;
  };
  */

  const handleProgramming = (_departure: any) => {
    departure.value = _departure;
    const routeData = router.resolve({
      name: 'adventure-departure-programming',
      params: { id: _departure._id },
    });
    window.open(routeData.href, '_blank');
  };

  const handleFile = (_departure: any) => {
    departure.value = _departure;
    const routeData = router.resolve({
      name: 'adventure-departure-file',
      params: { id: _departure._id },
    });
    window.open(routeData.href, '_blank');
  };

  const handleDelete = (_departure: any) => {
    Modal.confirm({
      title: '¿Está seguro de desactivar la salida?',
      icon: createVNode(ExclamationCircleOutlined),
      content: 'No se podrá recuperar la información.',
      okText: 'Sí, desactivar la salida',
      okType: 'danger',
      cancelText: 'Cancelar',
      async onOk() {
        await deactivateDeparture(_departure._id);
        if (!error.value) {
          await fetchDepartures();
        }
      },
      onCancel() {
        console.log('Cancel');
      },
    });
  };

  const handleChange = async (_pagination: any) => {
    console.log(_pagination);
    pagination.value = _pagination;

    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);

    await fetchDepartures();
  };

  const handleRemovePax = (index: number) => {
    const pax = paxs.value[index];

    if (!pax._id) {
      Modal.confirm({
        title: '¿Está seguro de eliminar el pasajero?',
        icon: createVNode(ExclamationCircleOutlined),
        content: 'No se podrá recuperar la información.',
        okText: 'Sí, eliminar el pasajero',
        okType: 'danger',
        cancelText: 'Cancelar',
        async onOk() {
          if (pax) {
            const { file, documentNumber, id } = pax;
            const params = {
              file,
              pax: {
                id,
                documentNumber,
              },
            };
            await deletePaxToDeparture(params);
            await fetchDepartures();
          }

          paxs.value.splice(index, 1);
        },
        onCancel() {
          console.log('Cancel');
        },
      });
    } else {
      paxs.value.splice(index, 1);
    }
  };

  const handleUpdateCash = async (id: string, status: string) => {
    await updateCash(id, status);

    if (error.value) {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    } else {
      handleCancel();
      await fetchDepartures();
      notification.success({
        message: 'Éxito',
        description: 'Se actualizó correctamente el requerimiento de efectivo',
      });
    }
  };

  const handleProcessCash = async (type: any) => {
    await processGenerateCash(type);

    if (error.value) {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    } else {
      handleCancel();
      await fetchDepartures();
      notification.success({
        message: 'Éxito',
        description: 'Se generó correctamente el requerimiento de efectivo',
      });
    }
  };

  const handleResendNotification = async (cash: any) => {
    await resendNotification(cash._id, cash.status);

    if (!error.value) {
      notification.success({
        message: 'Éxito',
        description: 'Se envió correctamente la notificación',
      });
    } else {
      notification.error({
        message: 'Error',
        description: error.value,
      });
    }
  };

  const handleAddAllPaxs = () => {
    // 1. Creamos una copia de los seleccionados con los nuevos datos
    // Esto evita modificar el estado original mientras iteramos
    const updatedSelectedPaxs = paxsSelected.value.map((pax: any) => ({
      ...pax,
      nrofile: searchFile.value,
      selected: true,
    }));

    // 2. Actualizamos la referencia de paxsSelected
    paxsSelected.value = updatedSelectedPaxs;

    // 3. Combinamos con lo que ya había en 'paxs', evitando duplicados si fuera necesario
    // Usamos el adapter para que el formato sea el correcto
    const newPaxsList = [
      ...paxs.value,
      ...updatedSelectedPaxs.map((pax: any) => createPaxAdapter(pax.file, pax)),
    ];

    // 4. Ordenamos y asignamos UNA SOLA VEZ al final
    paxs.value = sortPaxs(newPaxsList);
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
