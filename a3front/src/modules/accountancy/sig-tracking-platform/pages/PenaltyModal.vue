<!-- PenaltyModal.vue -->
<template>
  <a-modal
    :open="show"
    :title="title"
    width="600px"
    :confirm-loading="loadingSave"
    @ok="save"
    @cancel="close"
    :ok-button-props="{ disabled: !isFormValid }"
    :cancel-button-props="{ disabled: loadingSave }"
  >
    <a-form :model="formData" layout="vertical">
      <a-row :gutter="16">
        <a-col :span="12">
          <a-form-item label="Tipo de Servicio" required>
            <a-select
              v-model:value="formData.categoria"
              placeholder="Seleccionar..."
              :options="categoriesOptions"
              show-search
              :filter-option="false"
              @search="filterCategories"
              @change="filterTypeSanctions"
            />
          </a-form-item>
        </a-col>

        <a-col :span="12">
          <a-form-item label="Categoría" required>
            <a-select
              v-model:value="formData.tipo_sancion"
              placeholder="Seleccionar..."
              :options="typeSanctionsOptions"
              :disabled="!typeSanctions.length"
            />
          </a-form-item>
        </a-col>

        <a-col :span="12">
          <a-form-item label="Subcategoría" required>
            <a-input v-model:value="formData.subcategoria" placeholder="Ingrese subcategoría" />
          </a-form-item>
        </a-col>

        <a-col :span="12">
          <a-form-item label="Tipo de Sanción" required>
            <a-select
              v-model:value="formData.tipo_servicio"
              placeholder="Seleccionar..."
              :options="typesOptions"
              @change="handleTypeServiceChange"
            />
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
  </a-modal>
</template>

<script setup>
  import { ref, computed, reactive, onMounted } from 'vue';
  import { maintenanceSanctionsApi, nonConformingProductsApi } from '../../services/api';

  const props = defineProps({
    penalty: {
      type: Object,
      default: null,
    },
    show: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['close', 'saved']);

  // Estados
  const loadingSave = ref(false);
  const types = ref([]);
  const categories = ref([]);
  const typeSanctions = ref([]);
  const filteredCategories = ref([]);
  let user_ = '';

  // Form data
  const formData = reactive({
    tipo_servicio: '',
    categoria: '',
    subcategoria: '',
    tipo_sancion: '',
  });

  // Computed
  const hasLoadedPenaltyData = ref(false);
  const title = computed(() => {
    if (props.penalty?.rowid) {
      // Evitar loop infinito - solo cargar una vez
      if (!hasLoadedPenaltyData.value) {
        hasLoadedPenaltyData.value = true;
        loadPenaltyData();
      }
      return `Editar Sanción (${props.penalty.catref?.trim()})`;
    } else {
      // Reset cuando no hay penalty
      hasLoadedPenaltyData.value = false;
    }
    return 'Agregar Sanción';
  });

  const isFormValid = computed(() => {
    return (
      formData.tipo_servicio && formData.categoria && formData.subcategoria && formData.tipo_sancion
    );
  });

  const typesOptions = computed(() => {
    return types.value.map((type) => ({
      value: type.codigo,
      label: type.descripcion?.trim(),
    }));
  });

  const categoriesOptions = computed(() => {
    return filteredCategories.value.map((category) => ({
      value: category.codigo,
      label: category.descripcion?.trim(),
    }));
  });

  const typeSanctionsOptions = computed(() => {
    return typeSanctions.value.map((tSanction) => ({
      value: tSanction.codigo,
      label: tSanction.descnc?.trim(),
    }));
  });

  // Métodos
  const loadInitialData = async () => {
    try {
      if (types.value.length > 0) {
        console.log('ya hay datos');
        return;
      }

      const response = await maintenanceSanctionsApi.get('/init-penalties');
      const data = response.data.data.results;

      types.value = data.types || [];
      categories.value = data.categories || [];
      filteredCategories.value = categories.value;
    } catch (error) {
      console.error('Error al cargar datos iniciales:', error);
    }
  };

  const filterTypeSanctions = async () => {
    try {
      if (!formData.categoria) return;
      formData.tipo_sancion = '';
      const response = await nonConformingProductsApi.get(
        `/search-types?tipo_servicio=${formData.categoria}&usuario=${user_}`
      );
      typeSanctions.value = response.data.data || [];
    } catch (error) {
      console.error('Error al cargar filterTypeSanctions:', error);
    }
  };

  const loadPenaltyData = async () => {
    if (props.penalty?.rowid) {
      // Establecer valores directamente - los selects deben manejarlos
      formData.categoria = props.penalty.tipsvs?.trim() || '';
      formData.subcategoria = props.penalty.subcat?.trim() || '';
      formData.tipo_servicio = props.penalty.falta?.trim() || '';
      // Si hay categoría, cargar las typeSanctions
      if (formData.categoria) {
        await filterTypeSanctions();
        formData.tipo_sancion = props.penalty.catref?.trim() || '';
      }
    } else {
      // Reset form for new penalty
      Object.keys(formData).forEach((key) => {
        formData[key] = '';
      });
      typeSanctions.value = [];
    }
  };

  const handleTypeServiceChange = () => {
    // Filtrar categorías por tipo de servicio si es necesario
    filterCategories('');
  };

  const filterCategories = (searchText) => {
    if (!searchText) {
      filteredCategories.value = categories.value;
      return;
    }

    const search = searchText.toLowerCase();
    filteredCategories.value = categories.value.filter((category) =>
      category.descripcion?.toLowerCase().includes(search)
    );
  };

  const save = async () => {
    if (!isFormValid.value) return;

    try {
      loadingSave.value = true;

      const payload = {
        tipo_servicio: formData.tipo_servicio,
        categoria: formData.categoria,
        subcategoria: formData.subcategoria,
        tipo_sancion: formData.tipo_sancion,
      };

      let response;
      if (props.penalty?.rowid) {
        payload.codigo = props.penalty.rowid;
        response = await maintenanceSanctionsApi.put('/edit-penalty', payload);
      } else {
        response = await maintenanceSanctionsApi.post('/add-penalty', payload);
      }

      if (response.data.data === 'success') {
        emit('saved');
      } else {
        console.error('Error al guardar sanción:', response.data.data);
      }
    } catch (error) {
      console.error('Error al guardar sanción:', error);
    } finally {
      loadingSave.value = false;
    }
  };

  const close = () => {
    emit('close');
  };

  // Lifecycle
  onMounted(() => {
    loadInitialData();
  });
</script>
