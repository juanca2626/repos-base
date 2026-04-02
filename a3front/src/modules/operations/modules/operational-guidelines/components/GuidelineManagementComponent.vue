<template>
  <a-drawer
    v-model:open="childrenDrawer_1"
    :title="editModalGuideline ? 'Editar Pauta Operativa' : 'Crear Pauta Operativa'"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawer"
  >
    <a-alert
      :style="{ padding: '10px' }"
      message="Solo se deben ingresar pautas que se consideren distintas a la configuración de LimaTours."
      type="info"
      show-icon
      closable
    />

    <a-form
      layout="vertical"
      v-bind="{}"
      class="my-3"
      :model="formState"
      @submit.prevent="submitForm"
    >
      <a-row :gutter="16">
        <a-col :span="24">
          <a-form-item label="Sede:">
            <!-- label-in-value -->
            <!-- :disabled="editModalGuideline" -->
            <a-select
              v-model:value="selectedHeadquarter"
              :options="headquartersOptions ?? []"
              @change="handleHeadquarterChange"
              :disabled="editModalGuideline"
              label-in-value
            />
          </a-form-item>
        </a-col>

        <a-col :span="24">
          <a-form-item label="Tipo de pauta:">
            <a-select
              v-model:value="selectedGuideline"
              :options="guidelinesOptions ?? []"
              @change="handleGuidelineChange"
              :disabled="editModalGuideline"
              label-in-value
            />
          </a-form-item>
        </a-col>

        <a-col
          v-for="(option, index) in selectedGuideline.data.options"
          :key="index"
          :span="24"
          class="mb-3"
        >
          <template v-if="isOneSelect(option)">
            <template v-if="option.element === 'radio'">
              <a-radio-group v-model:value="formState.values[index]">
                <a-radio v-for="(v, i) in option.values" :key="i" :value="v.value">
                  {{ v.description }}
                </a-radio>
              </a-radio-group>
            </template>

            <template v-else-if="option.element === 'checkbox'">
              <a-checkbox
                v-for="(v, i) in option.values"
                :key="i"
                v-model:checked="formState.values[index]"
                >{{ v.description }}
              </a-checkbox>
            </template>

            <template v-else-if="option.element === 'textarea'">
              <a-textarea
                v-model:value="formState.values[index]"
                show-count
                :maxlength="300"
                :auto-size="{ minRows: 3, maxRows: 6 }"
              />
            </template>

            <template v-else>Opción no encontrada.</template>
          </template>

          <template v-if="isMultiSelect(option)">
            <a-form-item :label="option.description">
              <a-select
                placeholder="Buscar proveedores por nombre o código"
                :class="option.category"
                v-model:value="formState.values[index]"
                :show-arrow="true"
                show-search
                style="width: 100%"
                :filter-option="false"
                :options="state.data[index]"
                @search="(value: string) => fetchProvider(index, value)"
                @change="
                  (selectedValues: string[]) => validateSelection(formState, selectedValues, index)
                "
                mode="multiple"
              >
                <template v-if="state.fetching[index]" #notFoundContent>
                  <a-spin size="small" />
                </template>
                <template #suffixIcon>
                  <SearchOutlined />
                </template>
              </a-select>
            </a-form-item>
          </template>
        </a-col>

        <a-col v-if="selectedGuideline.data.observations" :span="24">
          <a-form-item label="Observaciones:">
            <a-textarea
              v-model:value="formState.observations"
              placeholder="Descripción"
              show-count
              :maxlength="250"
              :auto-size="{ minRows: 3, maxRows: 6 }"
            />
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>

    <template #footer>
      <a-row>
        <a-col :span="24">
          <a-button type="primary" block @click="submitForm" :loading="loading">Guardar</a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
  <div v-if="childrenDrawer_1" class="overlay"></div>
</template>

<script lang="ts" setup>
  import { onMounted, reactive, ref, watch, watchEffect } from 'vue';
  import { storeToRefs } from 'pinia';
  import { debounce } from 'lodash-es';
  import { SearchOutlined } from '@ant-design/icons-vue';

  import type { Guideline, Headquarter } from '../interfaces';

  import { useDrawerStore } from '@operations/modules/operational-guidelines/store/drawer.store';
  import { useDataStore } from '@operations/modules/operational-guidelines/store/data.store';
  import { useFormStore } from '@operations/modules/operational-guidelines/store/form.store';
  import { useOptionsStore } from '@operations/modules/operational-guidelines/store/options.store';
  import { validateSelection } from '@operations/modules/operational-guidelines/utils/validation';

  // drawer.store.ts
  const drawerStore = useDrawerStore();
  const { editModalGuideline } = storeToRefs(drawerStore);

  //data.store.ts
  const dataStore = useDataStore();
  const { getProviders } = dataStore;
  const { providers } = storeToRefs(dataStore);

  //options.store.ts
  const optionsStore = useOptionsStore();
  const { headquartersOptions, guidelinesOptions } = storeToRefs(optionsStore);

  //form.store.ts
  const formStore = useFormStore();
  const { formState, submitForm, isOneSelect, isMultiSelect } = formStore;
  const { selectedGuideline, selectedHeadquarter, initialData, loading } = storeToRefs(formStore);

  // Watch para asignar la primera opción de Headquarters cuando cambien las opciones
  watch(
    () => headquartersOptions.value,
    async (newOptions: any) => {
      if (newOptions.length > 0) {
        await optionsStore.setFirstHeadquarterOption(); // Usa la función creada anteriormente
      }
    },
    { immediate: true } // Para que se ejecute inmediatamente si ya hay opciones
  );

  // Watch para asignar la primera opción de Guidelines cuando cambien las opciones
  watch(
    () => guidelinesOptions.value,
    async (newOptions: any) => {
      if (newOptions.length > 0) {
        await optionsStore.setFirstGuidelineOption(); // Usa la función creada anteriormente
      }
    },
    { immediate: true } // Para que se ejecute inmediatamente si ya hay opciones
  );

  // ****************************** //

  // Watch adicional para asegurar la consistencia del objeto
  watch(selectedGuideline, (newOptions) => {
    if (newOptions?.option) {
      const firstOption = newOptions[0];
      selectedGuideline.value = {
        key: firstOption.key,
        value: firstOption.value,
        label: firstOption.label,
        data: firstOption.option.data,
      };
    }
  });

  const handleHeadquarterChange = (value: unknown) => {
    selectedHeadquarter.value = {
      key: value.key,
      value: value.value,
      label: value.label,
    };
  };

  // Función para normalizar el selectedGuideline después de seleccionar un nuevo valor
  const handleGuidelineChange = (value: unknown) => {
    // Desestructuramos los datos que necesitamos
    const { key, value: guidelineValue, label, option } = value;
    const { data } = option ?? {};

    // Normalizamos el objeto selectedGuideline
    selectedGuideline.value = {
      key,
      value: guidelineValue,
      label,
      data: data || {},
    };

    // Si el primer elemento no es un radio, limpiamos los valores
    if (data?.options?.[0]?.element !== 'radio') {
      formState.values = [];
    }

    // Recorremos las opciones y seleccionamos el primer valor si es de tipo 'radio'
    data?.options?.forEach((option: unknown, index: number) => {
      if (option.element === 'radio' && option.values.length > 0) {
        formState.values[index] = option.values[0].value;
      }
    });

    // Limpiamos las observaciones
    formState.observations = '';
  };

  const state = reactive({
    data: {} as Record<number, unknown[]>, // Almacena los datos de cada select individualmente
    value: {} as Record<number, string[]>, // Almacena el valor seleccionado para cada select
    fetching: {} as Record<number, boolean>, // Maneja el estado de carga de cada select
  });

  // Observa los cambios en initialData.value
  watch(
    () => initialData.value,
    (newValue) => {
      // Verifica que newValue sea un objeto y que tenga las propiedades options y selectedOptions
      if (newValue && Array.isArray(newValue.options) && newValue.options.length > 0) {
        newValue.options.forEach((v: unknown, i: number) => {
          state.data = {
            ...state.data,
            [i]: v ?? [], // Asigna opciones o un arreglo vacío si no existen
          };
        });
      }

      if (
        newValue &&
        Array.isArray(newValue.selectedOptions) &&
        newValue.selectedOptions.length > 0
      ) {
        newValue.selectedOptions.forEach((v: unknown, i: number) => {
          formState.values = {
            ...formState.values,
            [i]: v ?? [],
          };
          formState.observations = newValue.observations;
        });
      }
    },
    { immediate: true }
  );

  const fetchProvider = debounce(async (index: number, value: string) => {
    state.data[index] = []; // Limpia los datos previos para este select específico
    state.fetching[index] = true; // Establece el estado de carga para este select
    // console.log(selectedGuideline.value);

    // Accede a los valores de selectedGuideline
    const { value: type } = selectedGuideline.value || {};

    await getProviders(value.toUpperCase(), type);
    const data = providers.value.map((provider) => ({
      label: `${provider.code} - ${provider.fullname}`,
      value: provider._id,
      provider,
    }));
    state.data[index] = data; // Asigna los datos obtenidos al select correspondiente
    state.fetching[index] = false; // Cambia el estado de carga para este select
  }, 300);

  interface Props {
    showDrawer: boolean;
  }
  const props = defineProps<Props>();

  const emit = defineEmits(['handlerShowDrawer', 'otherEvents']);

  const childrenDrawer_1 = ref<boolean>(props.showDrawer);
  // const loading = ref(false);

  watch(
    () => props.showDrawer,
    (newVal) => {
      childrenDrawer_1.value = newVal;

      if (newVal && !editModalGuideline.value) {
        // Si estamos creando una nueva pauta
        // Asignar valor por defecto al primer radio cuando se abre el drawer
        selectedGuideline.value?.data?.options.forEach((option: unknown, index: number) => {
          if (option.element === 'radio' && option.values.length > 0) {
            formState.values[index] = option.values[0].value;
          }
        });
      }
    }
  );

  // const showChildrenDrawer_to1 = () => {
  //   restartNewBlock();
  //   childrenDrawer_1.value = true;
  // };

  // const restartNewBlock = () => {};

  const handlerShowDrawer = () => {
    emit('handlerShowDrawer', false);
  };

  // Headquarters
  watchEffect(() => {
    const headquarterOptions = headquartersOptions.value ?? [];
    if (headquarterOptions.length > 0 && !formState.headquarter.code) {
      const firstOption = headquarterOptions[0];
      formState.headquarter = {
        _id: firstOption._id,
        code: firstOption.value,
        description: firstOption.label,
      } as Headquarter;
    }
  });

  // Guidelines
  watchEffect(() => {
    const guidelineOptions = guidelinesOptions.value ?? [];
    if (guidelineOptions.length > 0 && !formState.guideline.code) {
      const firstOption = guidelineOptions[0];
      formState.guideline = {
        _id: firstOption._id,
        code: firstOption.value,
        description: firstOption.label,
      } as Guideline;
    }
  });

  onMounted(async () => {
    await dataStore.getHeadquarters();
    await dataStore.getGuidelines();
  });
</script>

<style lang="scss" scoped>
  ::v-deep(span.anticon) {
    svg {
      fill: #5c5ab4;
    }
  }

  .alert-message {
    padding: 10px;
  }
  .overlay {
    position: fixed;
    top: 0;
    left: 0;
    z-index: 10;
    background-color: rgba(0, 0, 0, 0.5);
    width: 100%;
    height: 100%;
  }

  /* Estilos en SCSS */
  ::v-deep(.blocked.ant-select-multiple) {
    .ant-select-selection-item {
      background-color: #fff2f2;
    }
  }

  ::v-deep(.preferred.ant-select-multiple) {
    .ant-select-selection-item {
      background-color: #dfffe9;
    }
  }
</style>
