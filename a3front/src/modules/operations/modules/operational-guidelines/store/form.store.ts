import { defineStore, storeToRefs } from 'pinia';
import { onMounted, reactive, ref, watch } from 'vue';
import { debounce } from 'lodash-es';

import { addOperationalGuideline } from '@operations/modules/operational-guidelines/api/operationalGuidelinesApi';

import type { Guideline, Headquarter, Owner } from '../interfaces';
import { useDrawerStore } from './drawer.store';
import { useDataStore } from './data.store';
import { useOptionsStore } from './options.store';
import { message } from 'ant-design-vue';

// drawer.store.ts
// const drawerStore = useDrawerStore();
// const { handlerShowDrawer } = useDrawerStore();
// const { showDrawer } = storeToRefs(drawerStore);

// //data.store.ts
// const dataStore = useDataStore();
// const { getProviders, getOwners, getOperationalGuidelines } = dataStore;
// const { providers, owner, operationalGuidelines } = storeToRefs(dataStore);

// //options.store.ts
// const optionsStore = useOptionsStore();
// const { ownersOptions, guidelinesOptions } = storeToRefs(optionsStore);

// interface FormState {
//   headquarter: Headquarter;
//   guideline: Guideline;
//   options: Array<{
//     element: string;
//     type: string;
//     selectedValues: string[]; // Almacena los valores seleccionados
//     data: any[]; // Almacena las opciones de cada select
//     fetching: boolean; // Estado de carga individual para cada select
//   }>;
//   values: Record<number, number | string>; // Mueve values dentro de formState
//   observations: '';
// }

export const useFormStore = defineStore('formStore', () => {
  // const drawerStore = useDrawerStore();
  const { handlerShowDrawer } = useDrawerStore();
  // const { showDrawer, editModalGuideline } = storeToRefs(drawerStore);

  const dataStore = useDataStore();
  const { getProviders, getOwners, getOperationalGuidelines } = dataStore;
  const { providers, owner, operationalGuidelines } = storeToRefs(dataStore);

  const optionsStore = useOptionsStore();
  const { ownersOptions } = storeToRefs(optionsStore);

  // const { guidelines } = storeToRefs(dataStore);
  // const selectedGuideline = ref<Guideline>({} as Guideline);
  const values = ref<{ [key: number]: number | string }>({});
  const initialData = ref<any>({});
  const loading = ref<boolean>(false);

  // const formState: FormState = reactive({
  //   headquarter: { _id: '', code: '', description: '' } as Headquarter,
  //   guideline: { _id: '', code: '', description: '' } as Guideline,
  //   options: [], // Aquí se almacenarán las opciones seleccionadas y sus datos
  //   values: {}, // Inicializa el estado values dentro de formState
  //   observations: '',
  // });

  // Función para obtener el estado inicial del formulario
  const getInitialFormState = () => {
    return {
      headquarter: { _id: '', code: '', description: '' } as Headquarter,
      guideline: { _id: '', code: '', description: '' } as Guideline,
      values: [],
      observations: '',
    };
  };

  const getInitialSelected = () => {
    return { key: '', value: '', label: '' };
  };

  const formState = reactive(getInitialFormState());

  const selectedOwner = ref<{ key: string; value: string; label: string } | null>(null);
  const selectedHeadquarter = ref(getInitialSelected());
  const selectedGuideline = ref<{
    key: string;
    value: string;
    label: string;
    data: unknown;
  } | null>(null);

  const state = reactive({
    data: {} as Record<number, unknown[]>, // Almacena los datos de cada select individualmente
    value: {} as Record<number, string[]>, // Almacena el valor seleccionado para cada select
    fetching: {} as Record<number, boolean>, // Maneja el estado de carga de cada select
  });

  const fetchProvider = debounce(async (index: number, value: string) => {
    state.data[index] = []; // Limpia los datos previos para este select específico
    state.fetching[index] = true; // Establece el estado de carga para este select
    console.log(state);
    await getProviders(value.toUpperCase());
    const data = providers.value.map((provider) => ({
      label: `${provider.code} - ${provider.fullname}`,
      value: provider._id,
      provider,
    }));
    console.log('🚀 ~ data ~ data:', data);
    state.data[index] = data; // Asigna los datos obtenidos al select correspondiente
    state.fetching[index] = false; // Cambia el estado de carga para este select
  }, 300);

  const isOneSelect = (option: any) =>
    ((option.element === 'radio' || option.element === 'select' || option.element === 'checkbox') &&
      option.type === 'oneselect') ||
    (option.element === 'textarea' && option.type === 'text');

  const isMultiSelect = (option: any) =>
    option.element === 'select' && option.type === 'multiselect';

  const transformData = (owner: any, input: any) => {
    console.log('🚀 ~ transformData ~ owner:', owner);
    console.log('🚀 ~ transformData ~ input:', input);
    return {
      owner_id: owner._id,
      headquarters: input.headquarters.map((hq: any) => ({
        headquarter_id: hq.headquarter._id,
        guidelines: hq.guidelines.map((gl: any) => {
          // Construye el objeto guideline con el campo observations opcional
          const transformedGuideline: any = {
            guideline_id: gl.guideline._id,
            options: gl.options.map((opt: any) => {
              // Checa si la opción tiene entidades, de lo contrario, transforma los valores
              if (opt.entities.length > 0) {
                return {
                  category: opt.category,
                  entities: opt.entities.map((entity: any) => entity._id),
                };
              } else {
                return {
                  category: opt.category,
                  values: opt.values,
                };
              }
            }),
          };

          // Solo agrega el campo observations si existe
          if (gl.observations) {
            transformedGuideline.observations = gl.observations;
          }

          return transformedGuideline;
        }),
      })),
    };
  };

  // Función para encontrar el nodo específico dentro de la estructura de data generada por transformData y obtener sus índices
  const findNodeWithIndexes = (data: any, headquarter_id: string, guideline_id: string): any => {
    // Buscar el índice del headquarter que coincida con el headquarter_id del selectedHeadquarter
    const headquarterIndex = data.headquarters.findIndex(
      (hq: any) => hq.headquarter_id === headquarter_id
    );

    // Si no se encuentra el headquarter, devolver todos los valores como null
    if (headquarterIndex === -1) {
      return {
        headquarter: null,
        guideline: null,
        headquarterIndex: null,
        guidelineIndex: null,
      };
    }

    // Obtener el headquarter usando el índice encontrado
    const headquarter = data.headquarters[headquarterIndex];

    // Buscar el índice del guideline dentro del headquarter encontrado que coincida con el guideline_id del selectedGuideline
    const guidelineIndex = headquarter.guidelines.findIndex(
      (gl: any) => gl.guideline_id === guideline_id
    );

    // Si no se encuentra el guideline, devolver guideline y guidelineIndex como null
    if (guidelineIndex === -1) {
      return {
        headquarter,
        guideline: null,
        headquarterIndex,
        guidelineIndex: null,
      };
    }

    // Obtener el guideline usando el índice encontrado
    const guideline = headquarter.guidelines[guidelineIndex];

    // Retornar el headquarter, el guideline y sus índices
    return { headquarter, guideline, headquarterIndex, guidelineIndex };
  };

  const editGuideline = async (headquarter_id: string, guideline_id: string) => {
    console.log('🚀 ~ editGuideline ~ headquarter_id:', headquarter_id);
    console.log('🚀 ~ editGuideline ~ guideline_id:', guideline_id);
    try {
      // Clona los datos originales para evitar modificar directamente el estado reactivo
      // editModalGuideline.value = true;
      const oG = operationalGuidelines.value;

      // Supongamos que headquarterId es el valor de la variable headquarter._id que buscas
      // const headquarterId = '66afbcec6b951361b598326e'; // Ejemplo de valor
      const result = oG.headquarters.find((hq: any) => hq.headquarter._id === headquarter_id);
      const headquarterNode = result.headquarter;
      const {
        guideline: guidelineNode,
        options: data,
        observations,
      } = result.guidelines.find((gl: any) => gl.guideline._id === guideline_id);

      initialData.value = {
        selectedOptions: data.map((v: any, i: number) => {
          if (guidelineNode.options[i].entity) return v.entities.map((entity: any) => entity._id);
          else return v.values[i];
        }),
        options: data.map((v: any) =>
          v.entities.map((entity: any) => ({
            value: entity._id,
            label: `${entity.code} - ${entity.fullname}`,
            provider: {},
          }))
        ),
        observations,
      };

      selectedHeadquarter.value = {
        key: headquarter_id,
        value: headquarterNode.code,
        label: headquarterNode.description,
      };

      selectedGuideline.value = {
        key: guideline_id,
        value: guidelineNode.code,
        label: guidelineNode.description,
        data: {
          options: guidelineNode.options,
          observations: guidelineNode.observations,
        },
      };

      handlerShowDrawer(true, true);
      return;
    } catch (error) {
      console.error('Error al editar el guideline:', error);
    }
  };

  const deleteGuideline = async (headquarter_id: string, guideline_id: string) => {
    try {
      const oG = operationalGuidelines.value;
      const oW = owner.value;
      // Transforma los datos originales usando transformData para obtener formData
      const formData: any = transformData(oW, oG);

      // Encontrar el headquarter correspondiente en formData
      const headquarter = formData.headquarters.find(
        (hq: any) => hq.headquarter_id === headquarter_id
      );

      if (!headquarter) {
        console.error(`No se encontró el headquarter con ID ${headquarter_id}`);
        return formData; // Retorna formData sin cambios si no se encuentra el headquarter
      }

      // Encontrar el índice del guideline dentro del headquarter
      const guidelineIndex = headquarter.guidelines.findIndex(
        (guideline: any) => guideline.guideline_id === guideline_id
      );

      if (guidelineIndex === -1) {
        console.error(`No se encontró el guideline con ID ${guideline_id}`);
        return formData; // Retorna formData sin cambios si no se encuentra el guideline
      }

      // Eliminar el guideline del array de guidelines en formData
      headquarter.guidelines.splice(guidelineIndex, 1);

      console.log(`Eliminado guideline con ID ${guideline_id} de headquarter ${headquarter_id}`);

      // Retornar el formData modificado
      // console.log(formData);
      // throw new Error('Test error: deleteGuideline');

      // Envío de datos
      loading.value = true;
      await addOperationalGuideline(formData);
      await handleSearch();
      message.success('La pauta operativa ha sido guardada con éxito.');
      loading.value = false;
      handlerShowDrawer(false);
    } catch (error) {
      console.error('Error al eliminar el guideline:', error);
      message.error('Hubo un error al eliminar la pauta operativa. Por favor, intenta nuevamente.');
      // Agregar notificación de error si es necesario
    }
  };

  // Método para enviar el formulario
  const submitForm = async () => {
    try {
      const oG = operationalGuidelines.value;
      const oW = owner.value;

      // Desestructuración de selectedHeadquarter y selectedGuideline, asegurando que no sean null o undefined
      const { key: headquarter_id = '' } = selectedHeadquarter.value ?? {};
      const { key: guideline_id = '', data: guideline_data } = selectedGuideline.value ?? {};

      //TODO: Revisar para optimizar el eliminar pauta
      if (guideline_data.options[0].element === 'checkbox' && !formState.values[0]) {
        await deleteGuideline(headquarter_id, guideline_id);
        // return { success: true, message: 'La pauta operativa se eliminó correctamente.' };
        return;
      }

      // Transformar los datos utilizando la función transformData
      const formData: any = !oG ? { owner_id: oW?._id, headquarters: [] } : transformData(oW, oG);
      console.log('🚀 ~ submitForm ~ formData:', formData);
      const result = findNodeWithIndexes(formData, headquarter_id, guideline_id);
      console.log('🚀 ~ submitForm ~ result:', result);

      // Función auxiliar para obtener y validar valores de formState.values[index]
      const getValidArray = (
        value: any,
        validTypes: string[],
        transformFn?: (item: any) => any
      ) => {
        if (!Array.isArray(value)) return [value];

        return value
          .filter((item) => validTypes.includes(typeof item))
          .map((item) => (transformFn ? transformFn(item) : item));
      };

      // Creando guideline y options
      const guideline: any = {
        guideline_id,
        options: guideline_data.options.map((opt: any, index: number) => {
          if (opt.entity) {
            const entities = getValidArray(formState.values[index], ['string']);
            return {
              category: opt.category,
              entities,
            };
          } else {
            const values = getValidArray(formState.values[index], ['number', 'string']);
            return {
              category: opt.category,
              values,
            };
          }
        }),
        observations: formState.observations,
      };

      const headquarter = {
        headquarter_id,
        guidelines: [guideline],
      };

      if (result.headquarterIndex === null) {
        formData.headquarters.push(headquarter);
      } else {
        if (result.guidelineIndex === null) {
          formData.headquarters[result.headquarterIndex].guidelines.push(guideline);
        } else {
          formData.headquarters[result.headquarterIndex].guidelines[result.guidelineIndex] =
            guideline;
        }
      }

      // throw new Error('Test error: submitForm');
      loading.value = true;
      await addOperationalGuideline(formData);
      await handleSearch();
      message.success('La pauta operativa ha sido guardada con éxito.');
      loading.value = false;
      handlerShowDrawer(false);
      // return;
      // const { status, statusText } = await addOperationalGuideline(formData);

      // if (status === 201) {
      //   await handleSearch();

      //   // selectedHeadquarter.value = getInitialSelected();
      //   await optionsStore.setFirstHeadquarterOption();
      //   await optionsStore.setFirstGuidelineOption();
      //   return { success: true, message: 'La pauta operativa ha sido guardada con éxito.' };
      // } else {
      //   throw new Error(`Error en la solicitud: ${statusText}`);
      // }
    } catch (error) {
      console.error('Error al guardar la pauta operativa:', error);
      message.error('Error al guardar la pauta operativa. Por favor, intenta nuevamente.');
      // console.error('Error al guardar la pauta operativa:', error);
      // return {
      //   success: false,
      //   message: 'Hubo un error al guardar la pauta operativa. Por favor, intenta nuevamente.',
      // };
    }
  };

  const formSearch = reactive({
    type: 'C',
    owner: { _id: '', code: '', name: '' } as Owner,
  });

  const handleSearch = async () => {
    //TODO: Revisar
    if (formSearch.owner._id || formSearch.owner.code) {
      // Llama a la función getOperationalGuidelines con el ID del owner seleccionado
      loading.value = true;
      await getOperationalGuidelines(formSearch.owner.code);
      loading.value = false;
    } else {
      message.error('Seleccione un cliente o mercado antes de buscar.');
      // Puedes agregar una notificación al usuario aquí si es necesario
    }
  };

  watch(
    selectedOwner,
    (newValue) => {
      if (newValue) {
        const selectedOption = ownersOptions.value?.find(
          (option) => option.value === newValue.value
        );
        if (selectedOption) {
          formSearch.owner._id = selectedOption.id;
          formSearch.owner.code = selectedOption.value as string;
          formSearch.owner.name = selectedOption.label.split(' - ')[1];
        }
      }
    },
    { immediate: true }
  );

  // Llamada inicial en onMounted
  onMounted(async () => {
    await getOwners(formSearch.type, '');
  });

  // Observa los cambios en formSearch.type para llamadas posteriores
  watch(
    () => formSearch.type,
    async (newType, query) => {
      loading.value = true;
      await getOwners(newType, query);
      loading.value = false;
    }
  );

  watch(
    () => ownersOptions.value,
    (newOptions: any) => {
      const firstOption = newOptions[0];
      if (newOptions.length === 0) selectedOwner.value = null;
      else {
        selectedOwner.value = {
          key: firstOption.key,
          value: firstOption.value,
          label: firstOption.label,
        };
      }
    }
  );

  return {
    // State
    loading,
    formSearch,
    formState,
    selectedOwner,
    selectedGuideline,
    selectedHeadquarter,
    initialData,
    // selectedOptions,
    // radioOptions,
    values,
    state,
    // Actions
    // change,
    handleSearch,
    fetchProvider,
    isOneSelect,
    isMultiSelect,
    submitForm, // Retorna el método para que pueda ser llamado desde el componente
    editGuideline,
    deleteGuideline,
  };
});
