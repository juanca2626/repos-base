<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Plantillas</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section mb-3">
      <div class="search-container">
        <a-row type="flex" justify="space-between" align="middle">
          <a-col :span="18">
            <a-form :model="filters" layout="vertical">
              <a-row :grutter="24" type="flex" justify="start" align="bottom" style="gap: 7px">
                <a-col :span="4">
                  <a-form-item label="Tipo:" name="value" class="mb-0">
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
                <a-col :span="4">
                  <a-form-item label="Duración:" name="value" class="mb-0">
                    <a-select
                      v-model:value="filters.duration"
                      placeholder="Seleccione una duración"
                      :show-search="false"
                      :allow-clear="false"
                      :options="durations"
                    >
                    </a-select>
                  </a-form-item>
                </a-col>
                <a-col :span="6">
                  <a-form-item label="Término de búsqueda:" name="value" class="mb-0">
                    <a-input
                      autocomplete="off"
                      v-model:value="filters.term"
                      placeholder="Ingrese algo para buscar.."
                    />
                  </a-form-item>
                </a-col>
                <a-col>
                  <a-button type="primary" :disabled="isLoading" @click="fetchTemplates">
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
          <a-col>
            <a-button type="primary" size="large" :disabled="isLoading" @click="handleAdd">
              <PlusOutlined /> Agregar Plantilla
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <!-- Tabla -->
    <backend-table-component
      ref="tableRef"
      :items="templates"
      :columns="columns"
      :options="tableOptions"
      :total-items="pagination.total"
      size="small"
      :rowClassName="rowClassName"
      @change="handleChange"
    >
      <template #name="{ record }">
        <a href="javascript:;" @click="handleServices(record)">
          <span class="text-uppercase" style="color: #373737"
            ><b>{{ record.name }}</b></span
          >
        </a>
      </template>
      <template #breakEvenPoint="{ record }">
        <template v-if="record.type === 'shared'">
          <template v-if="record.breakEvenPoint">
            <span
              ><b
                >{{ record.breakEvenPoint.pax }} PAX{{
                  record.breakEvenPoint.pax > 1 ? 'S' : ''
                }}</b
              >
              | <b>USD {{ record.breakEvenPoint.totalCost }}</b></span
            >
          </template>
          <template v-else>
            <span><b>-</b></span>
          </template>
        </template>
        <template v-else>
          <span><b>-</b></span>
        </template>
      </template>
      <template #actions="{ record }">
        <a-space>
          <a-dropdown>
            <template #overlay>
              <a-menu>
                <a-menu-item @click="handleEdit(record)"> <edit-outlined /> Editar </a-menu-item>
                <a-menu-item @click="handleClone(record)"> <copy-outlined /> Clonar </a-menu-item>
                <a-menu-item @click="handleServices(record)">
                  <ordered-list-outlined /> Servicios
                </a-menu-item>
                <a-menu-item @click="handleCostPAX(record)">
                  <dollar-outlined /> Costos por PAX
                </a-menu-item>
                <a-menu-item @click="handleBreakPoint(record)" :disabled="record.type !== 'shared'">
                  <dollar-outlined /> Punto de Equilibrio
                </a-menu-item>
                <a-menu-item @click="handleDelete(record)">
                  <delete-outlined /> Eliminar
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

  <!-- Modal para agregar/actualizar categoría -->
  <a-modal
    v-model:visible="showModal"
    title="PLANTILLA"
    :confirm-loading="isLoading"
    @ok="handleOk"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
    width="700px"
  >
    <a-form :model="template" :rules="rules" layout="vertical" ref="formRef">
      <a-form-item name="type" align="center">
        <a-radio-group
          v-model:value="template.type"
          button-style="solid"
          size="large"
          class="w-100"
        >
          <template v-for="type in types.filter((type: any) => type.value !== 'all')">
            <a-radio-button class="w-100 text-center" :value="type.value">{{
              type.label.toUpperCase()
            }}</a-radio-button>
          </template>
        </a-radio-group>
      </a-form-item>
      <a-row type="flex" justify="space-between" align="middle" style="gap: 10px">
        <a-col flex="auto">
          <a-form-item label="Tipo" name="durationType">
            <a-select
              v-model:value="template.durationType"
              placeholder="Seleccione un tipo"
              size="large"
              :show-search="false"
              :allow-clear="false"
              :options="durations.filter((duration: any) => duration.value !== 'all')"
            >
            </a-select>
          </a-form-item>
        </a-col>
        <template v-if="template.durationType === 'multiDay'">
          <a-col flex="auto">
            <a-form-item label="Días" name="durationDays">
              <a-input
                v-model:value="template.durationDays"
                type="number"
                placeholder="Días"
                size="large"
                autocomplete="off"
              />
            </a-form-item>
          </a-col>
          <a-col flex="auto">
            <a-form-item label="Noches" name="type">
              <a-input
                :value="`${template.durationDays - 1}`"
                type="number"
                placeholder="Noches"
                :readonly="true"
                size="large"
                autocomplete="off"
              />
            </a-form-item>
          </a-col>
        </template>
      </a-row>
      <a-row type="flex" justify="space-between" align="middle" style="gap: 10px">
        <a-col>
          <a-form-item label="Código" name="serviceCode">
            <a-input
              v-model:value="template.serviceCode"
              placeholder="Código"
              size="large"
              autocomplete="off"
            />
          </a-form-item>
        </a-col>
        <a-col flex="auto">
          <a-form-item label="Nombre" name="name">
            <a-input
              v-model:value="template.name"
              placeholder="Nombre"
              size="large"
              autocomplete="off"
            />
          </a-form-item>
        </a-col>
      </a-row>
      <a-row type="flex" justify="space-between" align="bottom" style="gap: 10px">
        <a-col :span="5">
          <a-form-item label="Fechas de Vigencia" name="startDate">
            <a-date-picker
              size="large"
              v-model:value="template.startDate"
              :format="dateFormat"
              value-format="YYYY-MM-DD"
              placeholder="Seleccione.."
            />
          </a-form-item>
        </a-col>
        <a-col :span="5">
          <a-form-item label="" name="endDate">
            <a-date-picker
              size="large"
              v-model:value="template.endDate"
              :format="dateFormat"
              value-format="YYYY-MM-DD"
              placeholder="Seleccione.."
            />
          </a-form-item>
        </a-col>
        <a-col :span="3">
          <a-form-item label="% OPE" name="percentOpe">
            <a-input
              v-model:value="template.percentOpe"
              size="large"
              placeholder="0"
              autocomplete="off"
            />
          </a-form-item>
        </a-col>
        <a-col>
          <a-form-item label="CNT. MAX. PAX" name="paxs">
            <a-input
              v-model:value="template.paxs"
              size="large"
              placeholder="0"
              autocomplete="off"
            />
          </a-form-item>
        </a-col>
      </a-row>

      <a-form-item label="Descripción">
        <quill-editor
          theme="snow"
          toolbar="essential"
          contentType="html"
          placeholder="Ingrese la descripción"
          v-model:content="template.description"
          editorStyle="height: 250px;"
        />
      </a-form-item>

      <a-form-item label="Itinerario">
        <quill-editor
          theme="snow"
          toolbar="essential"
          contentType="html"
          placeholder="Ingrese el itinerario"
          v-model:content="template.itinerary"
          editorStyle="height: 250px;"
        />
      </a-form-item>

      <a-form-item label="Restricciones">
        <a-textarea
          v-model:value="template.restrictions"
          size="large"
          placeholder="Ingrese las restricciones"
          autocomplete="off"
        ></a-textarea>
      </a-form-item>

      <a-form-item label="Cargar imágenes" class="mb-0">
        <file-upload-component
          title="Cargar imágenes"
          :links="template.images"
          v-bind:folder="'adventure/templates'"
          @onResponseFiles="responseFiles"
        />
      </a-form-item>
    </a-form>
  </a-modal>

  <a-modal
    v-model:visible="showModalClone"
    title="Clonar Plantilla"
    :confirm-loading="isLoading"
    @ok="handleOkClone"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
    width="700px"
  >
    <a-form :model="templateClone" :rules="rulesClone" layout="vertical" ref="formCloneRef">
      <a-alert type="warning">
        <template #description>
          <b>¡Importante!</b>
          <p class="mb-0">
            La clonación copia todos los servicios (generales o normales) y categorías que
            pertenecen a una plantilla.
          </p>
        </template>
      </a-alert>
      <a-form-item label="Nombre" name="description">
        <a-input
          v-model:value="templateClone.name"
          autocomplete="off"
          autofocus="true"
          placeholder="Ingrese el nombre"
          size="large"
        />
      </a-form-item>
    </a-form>
  </a-modal>

  <a-modal
    v-model:visible="showModalBreakPoint"
    title="Punto de Equilibrio"
    :confirm-loading="isLoading"
    @ok="handleOkBreakPoint"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
    width="400px"
  >
    <backend-table-component
      :items="breakpoints"
      :columns="columnBreakpoints"
      :options="tableOptionBreakpoints"
      size="small"
      :rowClassName="getRowClassName"
    >
      <template #actions="{ index }">
        <template v-if="breakpoints[index].selected || !breakpoints.some((b: any) => b.selected)">
          <a-tooltip placement="right" title="Marcar / Desmarcar como punto de equilibrio">
            <a-button
              :type="breakpoints[index].selected ? 'primary' : 'dashed'"
              danger
              @click="toggleBreakpoint(index)"
            >
              <template v-if="breakpoints[index].selected">
                <DeleteOutlined />
              </template>
              <template v-else>
                <PlusOutlined />
              </template>
            </a-button>
          </a-tooltip>
        </template>
      </template>
    </backend-table-component>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, onBeforeMount, createVNode } from 'vue';
  import { useTemplates } from '@/composables/adventure';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';
  import FileUploadComponent from '@/components/global/FileUploadComponent.vue';
  import {
    ExclamationCircleOutlined,
    PlusOutlined,
    ReloadOutlined,
    SearchOutlined,
    SettingFilled,
    EditOutlined,
    CopyOutlined,
    OrderedListOutlined,
    DollarOutlined,
    DeleteOutlined,
  } from '@ant-design/icons-vue';
  import { Modal, notification } from 'ant-design-vue';
  import { useRouter } from 'vue-router';

  const router = useRouter();

  const tableRef = ref();
  const dateFormat = 'DD/MM/YYYY';

  const {
    isLoading,
    template,
    templateClone,
    templates,
    pagination,
    filters,
    types,
    durations,
    // services,
    // cash,
    fetchTemplates,
    error,
    saveTemplateClone,
    saveTemplate,
    updateTemplate,
    deleteTemplate,
    breakpoints,
    fetchTemplateBreakpoints,
    saveTemplateBreakpoint,
  } = useTemplates();

  const clearFilters = async () => {
    filters.value = {
      type: 'all',
      duration: 'all',
      term: '',
    };

    await fetchTemplates();
  };

  const columns = [
    {
      title: 'Nombre',
      dataIndex: 'name',
      key: 'name',
      width: 300,
      isSlot: true,
    },
    {
      title: 'Refer',
      dataIndex: 'serviceCode',
      key: 'serviceCode',
      align: 'center',
      customRender: (value: any) => {
        return `<span><b>${value}</b></span>`;
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
      title: '% OPE',
      dataIndex: 'percentOpe',
      key: 'percentOpe',
      align: 'center',
    },
    {
      title: 'Punto Equilibrio',
      dataIndex: 'breakEvenPoint',
      key: 'breakEvenPoint',
      align: 'center',
      isSlot: true,
    },
    {
      title: 'PAX',
      dataIndex: 'paxs',
      key: 'paxs',
      align: 'center',
    },
    {
      title: 'Entradas',
      dataIndex: 'tickets',
      key: 'tickets',
      customRender: (value: any) => {
        let html = '';
        if (value.length > 0) {
          value.map((item: any) => {
            html += `<span class="d-block text-uppercase">${item.name}</span>`;
          });
          return html;
        }
        return '-';
      },
      align: 'center',
    },
    {
      title: 'Vigencia',
      dataIndex: 'validity',
      key: 'validity',
      align: 'center',
    },
    {
      title: 'Ult. Modificación',
      dataIndex: 'updatedAt',
      key: 'updatedAt',
      align: 'center',
    },
  ];

  const columnBreakpoints = [
    {
      title: 'PAX',
      dataIndex: 'pax',
      key: 'pax',
      align: 'center',
    },
    {
      title: 'PRECIO (USD)',
      dataIndex: 'cost',
      key: 'cost',
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: true,
    pagination: pagination.value,
    rowKey: '_id',
    bordered: true,
  };

  const tableOptionBreakpoints = {
    showActions: true,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const formRef = ref();
  const formCloneRef = ref();

  const rules = {
    type: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    name: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    description: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    durationType: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    serviceCode: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    percentOpe: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    paxs: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    startDate: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    endDate: [{ required: true, message: 'Requerido', trigger: 'blur' }],
  };
  const rulesClone = {
    name: [{ required: true, message: 'Requerido', trigger: 'blur' }],
  };

  const showModal = ref(false);
  const showModalClone = ref(false);
  const showModalBreakPoint = ref(false);

  onBeforeMount(async () => {
    await fetchTemplates();
  });

  const handleOk = async () => {
    try {
      await formRef.value.validate();

      if (template.value._id) {
        await updateTemplate(template.value._id, template.value);
      } else {
        await saveTemplate(template.value);
      }

      if (!error.value) {
        await fetchTemplates();
        showModal.value = false;
      }
    } catch (error) {
      console.error('Error al validar el formulario:', error);
    }
  };

  const handleOkClone = async () => {
    try {
      await formCloneRef.value.validate();
      await saveTemplateClone();
      if (!error.value) {
        await fetchTemplates();
        showModalClone.value = false;
      }
    } catch (error) {
      console.error('Error al validar el formulario:', error);
    }
  };

  const toggleBreakpoint = (index: number) => {
    breakpoints.value[index].selected = !breakpoints.value[index].selected;
  };

  const handleOkBreakPoint = async () => {
    try {
      const breakpoint = breakpoints.value.find((breakpoint: any) => breakpoint.selected);

      if (breakpoint) {
        await saveTemplateBreakpoint(breakpoint);
        if (!error.value) {
          await fetchTemplates();
          showModalBreakPoint.value = false;
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

  const handleCancel = () => {
    showModal.value = false;
    showModalClone.value = false;
    showModalBreakPoint.value = false;
  };

  const handleAdd = () => {
    template.value = {
      durationDays: 1,
      durationType: 'fullDay',
      serviceCode: '',
      name: '',
      startDate: '',
      endDate: '',
      percentOpe: 0,
      paxs: 1,
      description: '',
      itinerary: '',
      restrictions: '',
      type: 'private',
      images: [],
    };
    showModal.value = true;
  };

  const handleEdit = (_template: any) => {
    template.value = JSON.parse(JSON.stringify(_template));
    showModal.value = true;
  };

  const handleClone = (_template: any) => {
    templateClone.value.id = _template._id;
    showModalClone.value = true;
  };

  const handleServices = (_template: any) => {
    template.value = JSON.parse(JSON.stringify(_template));
    const routeData = router.resolve({
      name: 'adventure-template-services',
      params: { id: _template._id },
    });
    window.open(routeData.href, '_blank');
  };

  const handleCostPAX = (_template: any) => {
    template.value = JSON.parse(JSON.stringify(_template));
    router.push({
      name: 'adventure-template-cash',
      params: { id: _template._id },
    });
  };

  const handleBreakPoint = async (_template: any) => {
    template.value = JSON.parse(JSON.stringify(_template));
    await fetchTemplateBreakpoints();

    console.log(template.value.breakEvenPoint, breakpoints.value);
    const breakpoint = breakpoints.value.find(
      (breakpoint: any) => breakpoint.pax == template.value.breakEvenPoint.pax
    );
    breakpoint.selected = true;
    showModalBreakPoint.value = true;
  };

  const handleDelete = async (_template: any) => {
    Modal.confirm({
      title: '¿Está seguro de eliminar la plantilla?',
      icon: createVNode(ExclamationCircleOutlined),
      content:
        'El proceso eliminará la información de la plantilla, las categorías y los servicios que esta incluye. Los servicios generales no sufren cambio alguno y se mantienen para su uso normal en otras plantillas.',
      okText: 'Sí, eliminar',
      okType: 'danger',
      cancelText: 'Regresar',
      async onOk() {
        await deleteTemplate(_template._id);
        if (!error.value) {
          await fetchTemplates();
        }
      },
      onCancel() {
        console.log('Cancel');
      },
    });
  };

  const handleChange = async (_pagination: any) => {
    pagination.value = _pagination;

    tableRef.value.setPage(_pagination.current);
    tableRef.value.setPageSize(_pagination.perPage);

    await fetchTemplates();
  };

  const responseFiles = (files: any) => {
    template.value.files = files.map((item: any) => item.link);
  };

  const rowClassName = (record: any) => {
    if (record.type === 'private') {
      if (!record.hasServices) {
        return 'bg-danger';
      } else {
        return 'bg-green';
      }
    }

    if (record.type === 'shared') {
      if (!record.hasServices || !record.breakEvenPoint) {
        return 'bg-danger';
      } else {
        return 'bg-green';
      }
    }
  };

  const getRowClassName = (record: any) => {
    if (record.selected) {
      return 'bg-danger';
    }
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
