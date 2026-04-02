<template>
  <a-modal
    :open="open"
    :title="`Agregar un Producto no Conforme - NRO FILE: ${formData.nroref || ''}`"
    width="900px"
    :footer="null"
    @cancel="handleCancel"
  >
    <a-form layout="vertical" class="product-nc-form">
      <a-row :gutter="16">
        <!-- Nro File -->
        <a-col :span="12">
          <a-form-item label="NRO FILE">
            <a-input
              v-model:value="formData.nroref"
              :disabled="blocked"
              @change="handleSearchDetail"
              placeholder="Ej: 388826"
            />
          </a-form-item>
        </a-col>

        <!-- EC (Ejecutivo Comercial) -->
        <a-col :span="12">
          <a-form-item label="EC">
            <a-input
              v-model:value="formData.operad"
              :disabled="blocked || !!formData.nroref"
              placeholder="SAA"
            />
          </a-form-item>
        </a-col>

        <!-- Nombre RVA -->
        <a-col :span="12">
          <a-form-item label="NOMBRE RVA">
            <a-input
              v-model:value="formData.descri"
              :disabled="blocked || !!formData.nroref"
              placeholder="Nombre de la reserva"
            />
          </a-form-item>
        </a-col>

        <!-- Pasajero -->
        <a-col :span="12">
          <a-form-item label="PASAJERO">
            <a-select
              v-if="!blocked"
              v-model:value="selectedPassenger"
              show-search
              placeholder="Filtra por nombre"
              :filter-option="false"
            >
              <a-select-option v-for="p in allPassengers" :key="p.nrosec" :value="p.nrosec">
                {{ p.nrodoc }} - {{ p.nombre }}
              </a-select-option>
            </a-select>
            <a-input v-else :value="formData.nombre" disabled />
          </a-form-item>
        </a-col>

        <!-- Cliente -->
        <a-col :span="12">
          <a-form-item label="CLIENTE (NOMBRE)">
            <a-input
              v-model:value="formData.razon"
              :disabled="blocked || !!formData.nroref"
              placeholder="Nombre del cliente"
            />
          </a-form-item>
        </a-col>

        <!-- Área -->
        <a-col :span="12">
          <a-form-item label="ÁREA">
            <a-input
              v-model:value="formData.codsec"
              :disabled="blocked || !!formData.nroref"
              placeholder="Ej: C3N1"
            />
          </a-form-item>
        </a-col>

        <!-- Tipo de Servicio -->
        <a-col :span="8">
          <a-form-item label="TIPO DE SERVICIO">
            <a-select
              v-if="!blocked"
              v-model:value="selectedTypeService"
              placeholder="Filtra por descripción"
              @change="handleServiceTypeChange"
            >
              <a-select-option v-for="t in allTypeservices" :key="t.codgru" :value="t.codgru">
                {{ t.descri }}
              </a-select-option>
            </a-select>
            <a-input v-else :value="formData.servicio" disabled />
          </a-form-item>
        </a-col>

        <!-- Servicio -->
        <a-col :span="8">
          <a-form-item label="SERVICIO">
            <a-select
              v-if="!blocked"
              v-model:value="selectedService"
              show-search
              not-found-content="Selecciona un tipo de servicio para mostrar los proveedores"
              placeholder="Filtra por código o descripción"
              :filter-option="false"
              @change="handleServiceChange"
              @search="handleServiceSearch"
            >
              <a-select-option v-for="s in allServices" :key="s.prefac" :value="s.prefac">
                {{ s.prefac }} - {{ s.proveedor }}
              </a-select-option>
            </a-select>
            <a-input v-else :value="formData.proveedor_servicio" disabled />
          </a-form-item>
        </a-col>

        <!-- Fecha del Incidente -->
        <a-col :span="8">
          <a-form-item label="FECHA DEL INCIDENTE">
            <a-select
              v-if="fechasDisponibles.length > 0 && !manualDateMode"
              v-model:value="fechaDisponible"
              @change="handleDateSelectChange"
              placeholder="Seleccione de la lista"
            >
              <a-select-option v-for="t in fechasDisponibles" :key="t.fecin" :value="t.fecin">
                {{ t.fecin }}
              </a-select-option>
              <a-select-option disabled key="separator" class="ant-select-item-option-disabled">
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
              <a-tooltip
                v-if="fechasDisponibles.length > 0"
                title="Volver a la lista de fechas sugeridas"
              >
                <a-button @click="returnToDateList">
                  <template #icon><OrderedListOutlined /></template>
                </a-button>
              </a-tooltip>
            </div>
          </a-form-item>
        </a-col>

        <!-- Categoría -->
        <a-col :span="24">
          <a-form-item label="CATEGORÍA">
            <a-select
              v-model:value="selectedType"
              show-search
              placeholder="Filtra por categoría"
              not-found-content="Selecciona un tipo de servicio para mostrar las categorías"
              :filter-option="filterOption"
              @change="handleCategoryChange"
            >
              <a-select-option
                v-for="t in allTypes"
                :key="t.codigo"
                :value="t.codigo"
                :label="t.descnc"
              >
                {{ t.descnc }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>

        <!-- Subcategoría -->
        <a-col :span="24" v-if="selectedType">
          <a-form-item label="SUBCATEGORÍA">
            <a-alert
              v-if="allSubcategories.length === 0"
              message="Si no se muestra la opción de elegir subcategorías es porque la categoría escogida no tiene elementos agregados."
              type="warning"
              show-icon
            />
            <a-select
              v-else
              v-model:value="selectedSubcategory"
              show-search
              placeholder="Filtra por subcategoría"
              @change="handleSubcategoryChange"
            >
              <a-select-option v-for="s in allSubcategories" :key="s.subcat" :value="s.subcat">
                {{ s.subcat }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </a-col>

        <!-- Falta (conditional) -->
        <a-col :span="24" v-if="falta">
          <a-form-item label="FALTA">
            <a-input v-model:value="falta" disabled />
          </a-form-item>
        </a-col>

        <!-- Checkboxes -->
        <a-col :span="24" style="padding-bottom: 16px">
          <a-checkbox v-model:checked="infundado">INFUNDADO</a-checkbox>
          <a-checkbox v-model:checked="subjetivo" style="margin-left: 16px">SUBJETIVO</a-checkbox>
        </a-col>

        <!-- Comentario -->
        <a-col :span="24">
          <a-form-item label="COMENTARIO">
            <CloudinaryQuillEditor
              ref="commentEditorRef"
              v-model:modelValue="comment"
              :placeholder="'Ingrese el comentario...'"
              @image-upload-start="handleImageUploadStart"
              @image-upload-complete="handleImageUploadComplete"
            />
            <div v-if="imageUploading" style="margin-top: 8px">
              <a-alert message="Subiendo imágenes..." type="info" show-icon />
            </div>
          </a-form-item>
        </a-col>
      </a-row>

      <!-- Actions -->
      <div style="margin-top: 24px; text-align: right">
        <a-button @click="handleCancel" style="margin-right: 8px">Cancelar</a-button>
        <a-button type="primary" danger @click="handleSave" :loading="saving"> Guardar </a-button>
      </div>
    </a-form>
  </a-modal>
</template>

<script setup>
  import { ref, reactive, watch } from 'vue';
  import { debounce } from 'lodash-es';
  import { message } from 'ant-design-vue';
  import { OrderedListOutlined } from '@ant-design/icons-vue';
  import CloudinaryQuillEditor from '../../components/CloudinaryQuillEditor.vue';
  import { useProductNC } from '../composables/productNCComposable';

  const props = defineProps({
    open: {
      type: Boolean,
      default: false,
    },
    nrofile: {
      type: String,
      default: '',
    },
    codref: {
      type: Number,
      default: 0,
    },
  });

  const emit = defineEmits(['update:open', 'saved']);

  const {
    saving,
    searchFileDetail,
    getServiceTypes,
    getServices,
    getCategories,
    getSubcategories,
    getPassengers,
    getAvailableDates,
    formatDateForAPI,
    saveProductNC,
  } = useProductNC();

  // Form data
  const formData = reactive({
    codref: 0,
    nroref: '',
    operad: '',
    descri: '',
    nombre: '',
    razon: '',
    codsec: '',
    servicio: '',
    proveedor_servicio: '',
    codcli: '',
  });

  const blocked = ref(false);

  // Selections
  const selectedPassenger = ref('');
  const selectedTypeService = ref('');
  const selectedService = ref('');
  const selectedType = ref('');
  const selectedSubcategory = ref('');
  const fechaDisponible = ref('');
  const fechaDisponibleCalendario = ref(null);
  const manualDateMode = ref(false);

  // Data arrays
  const allPassengers = ref([]);
  const allTypeservices = ref([]);
  const allServices = ref([]);
  const allTypes = ref([]);
  const allSubcategories = ref([]);
  const fechasDisponibles = ref([]);

  // Additional fields
  const falta = ref('');
  const infundado = ref(false);
  const subjetivo = ref(false);
  const comment = ref('');

  // Editor refs
  const commentEditorRef = ref(null);
  const imageUploading = ref(false);

  // Watch for prop changes
  watch(
    () => props.open,
    (newVal) => {
      if (newVal) {
        initializeForm();
      }
    }
  );

  watch(
    () => props.nrofile,
    (newVal) => {
      if (newVal) {
        formData.nroref = newVal;
        handleSearchDetail();
      }
    },
    { immediate: true }
  );

  const initializeForm = async () => {
    // Load initial data
    allTypes.value = [];
    allServices.value = [];
    allSubcategories.value = [];
    comment.value = '';

    const [types] = await Promise.all([getServiceTypes()]);
    allTypeservices.value = types;

    if (props.nrofile) {
      formData.nroref = props.nrofile;
      await handleSearchDetail();
    }
  };

  const handleSearchDetail = debounce(async () => {
    if (!formData.nroref) return;

    const detail = await searchFileDetail(formData.nroref);
    if (detail && detail.data) {
      Object.assign(formData, detail.data);

      // Load passengers
      const passengers = await getPassengers(formData.nroref);
      allPassengers.value = passengers;
    }
  }, 350);

  const handleServiceTypeChange = async () => {
    selectedService.value = '';
    allServices.value = [];
    allTypes.value = [];

    if (selectedTypeService.value) {
      const [categories, services] = await Promise.all([
        getCategories(selectedTypeService.value),
        getServices(formData.nroref, selectedTypeService.value),
      ]);

      allServices.value = services;
      allTypes.value = categories;
    }
  };

  const handleServiceSearch = async (searchText) => {
    if (selectedTypeService.value) {
      const services = await getServices(selectedTypeService.value);
      allServices.value = services.filter(
        (s) =>
          s.prefac.toLowerCase().includes(searchText.toLowerCase()) ||
          s.proveedor.toLowerCase().includes(searchText.toLowerCase())
      );
    }
  };

  const handleServiceChange = async () => {
    if (formData.nroref && selectedService.value) {
      const dates = await getAvailableDates(formData.nroref, selectedService.value);
      fechasDisponibles.value = dates;
      if (dates.length === 0) {
        manualDateMode.value = true;
      }
    }
  };

  const handleCategoryChange = async () => {
    selectedSubcategory.value = '';
    allSubcategories.value = [];
    falta.value = '';

    if (selectedType.value) {
      const subcategories = await getSubcategories(selectedTypeService.value, selectedType.value);
      allSubcategories.value = subcategories;
    }
  };

  const handleSubcategoryChange = () => {
    const selected = allSubcategories.value.find((s) => s.subcat === selectedSubcategory.value);
    if (selected) {
      falta.value = selected.falta || '';
    }
  };

  const handleDateSelectChange = () => {
    if (fechaDisponible.value === 'MANUAL_ENTRY') {
      manualDateMode.value = true;
      fechaDisponible.value = '';
    }
  };

  const handleDatePickerChange = () => {
    // Date changed in calendar
  };

  const returnToDateList = () => {
    manualDateMode.value = false;
    fechaDisponibleCalendario.value = null;
  };

  const filterOption = (input, option) => {
    return option.label.toLowerCase().includes(input.toLowerCase());
  };

  const handleImageUploadStart = () => {
    imageUploading.value = true;
  };

  const handleImageUploadComplete = () => {
    imageUploading.value = false;
  };

  const checkAllImagesUploaded = async () => {
    if (commentEditorRef.value) {
      const commentFiles = commentEditorRef.value.getAllFiles();
      if (commentFiles.total !== commentFiles.completed) return false;
    }
    return true;
  };

  const handleSave = async () => {
    // Verify all images uploaded
    const allUploaded = await checkAllImagesUploaded();
    if (!allUploaded) {
      message.warning('Espere a que terminen de subir todas las imágenes');
      return;
    }

    const user_ = localStorage.getItem('user') || 'SYSTEM';

    const fechaIncidente =
      fechasDisponibles.value.length > 0 && !manualDateMode.value
        ? fechaDisponible.value
        : formatDateForAPI(fechaDisponibleCalendario.value);

    const data = {
      usuario: user_,
      nroref: formData.nroref,
      codcli: formData.codcli,
      passenger: selectedPassenger.value,
      type: selectedType.value,
      type_service: selectedTypeService.value,
      subcategory: selectedSubcategory.value,
      service: selectedService.value,
      comment: comment.value,
      response: '',
      date: fechaIncidente,
      estado: 'P-2',
      infundado: infundado.value ? 1 : 0,
      subjetivo: subjetivo.value ? 1 : 0,
      asociado: 0,
      flag_notify: 0,
      asumido_por: '',
      total_compensacion: 0,
      monto_compensacion: 0,
      monto_reembolso: 0,
      observaciones_compensacion: '',
      observaciones_reembolso: '',
      corepr: '',
      numfre: '',
      falta: falta.value,
      sancion: '',
      accion_sancion: '',
      autorizado_por: '',
    };

    const result = await saveProductNC(data);
    if (result.success) {
      emit('saved', result.data);
      handleCancel();
    }
  };

  const handleCancel = () => {
    emit('update:open', false);
    resetForm();
  };

  const resetForm = () => {
    Object.assign(formData, {
      codref: 0,
      nroref: '',
      operad: '',
      descri: '',
      nombre: '',
      razon: '',
      codsec: '',
      servicio: '',
      proveedor_servicio: '',
      codcli: '',
    });
    selectedPassenger.value = '';
    selectedTypeService.value = '';
    selectedService.value = '';
    selectedType.value = '';
    selectedSubcategory.value = '';
    fechaDisponible.value = '';
    fechaDisponibleCalendario.value = null;
    manualDateMode.value = false;
    falta.value = '';
    infundado.value = false;
    subjetivo.value = false;
    comment.value = '';
    allPassengers.value = [];
    allServices.value = [];
    allSubcategories.value = [];
    fechasDisponibles.value = [];
  };
</script>

<style scoped lang="scss">
  .product-nc-form {
    :deep(.ant-form-item) {
      margin-bottom: 16px;
    }

    :deep(.ant-form-item-label > label) {
      font-weight: 600;
      color: #374151;
    }
  }
</style>
