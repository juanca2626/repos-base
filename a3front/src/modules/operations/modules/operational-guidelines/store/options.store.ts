import { capitalizeFirstLetter } from '../utils/stringUtils';
import type { SelectProps } from 'ant-design-vue';
import { defineStore, storeToRefs } from 'pinia';
import { computed } from 'vue';
import type { Guideline, Headquarter, Owner } from '../interfaces';
import { useDataStore } from './data.store';

// Lazy-load formStore only when necessary
let formStore: any = null;

// Función para cargar formStore de forma dinámica
async function getFormStore() {
  if (!formStore) {
    const module = await import('./form.store'); // Carga el módulo dinámicamente
    formStore = module.useFormStore(); // Obtén la instancia del store
  }
  return formStore;
}

export const useOptionsStore = defineStore('optionsStore', () => {
  const dataStore = useDataStore();
  const { headquarters, guidelines, owners } = storeToRefs(dataStore);

  const ownersOptions = computed<SelectProps['options']>(() => {
    return Array.isArray(owners.value)
      ? owners.value.map((owner: Owner) => ({
          key: owner._id,
          value: owner.code,
          label: `${owner.code} - ${owner.name}`,
        }))
      : [];
  });

  const headquartersOptions = computed<SelectProps['options']>(() => {
    return Array.isArray(headquarters.value)
      ? headquarters.value.map((headquarter: Headquarter) => ({
          key: headquarter._id,
          value: headquarter.code,
          label: capitalizeFirstLetter(headquarter.description),
        }))
      : [];
  });

  const guidelinesOptions = computed<SelectProps['options']>(() => {
    return Array.isArray(guidelines.value)
      ? guidelines.value.map((guideline: Guideline) => ({
          key: guideline._id,
          value: guideline.code,
          label: guideline.description,
          data: {
            options: guideline.options ?? [],
            observations: guideline.observations ?? false,
          },
        }))
      : [];
  });

  // Función para asignar la primera opción disponible de headquarters a selectedHeadquarter
  const setFirstHeadquarterOption = async () => {
    const firstOption = headquartersOptions.value?.[0]; // Usa el encadenamiento opcional para manejar undefined
    if (firstOption) {
      const formStore = await getFormStore(); // Usa await para obtener formStore correctamente
      formStore.selectedHeadquarter = {
        key: firstOption.key,
        value: firstOption.value,
        label: firstOption.label,
      };
    }
  };

  // Función para asignar la primera opción disponible de guidelines a selectedGuideline
  const setFirstGuidelineOption = async () => {
    const firstOption = guidelinesOptions.value?.[0];
    if (firstOption) {
      const formStore = await getFormStore();
      formStore.selectedGuideline = {
        key: firstOption.key,
        value: firstOption.value,
        label: firstOption.label,
        data: firstOption.data,
      };
    }
  };

  const setFirstFormState = async () => {
    const formStore = await getFormStore();
    formStore.formState.observations = '';
  };

  const resetOptions = async () => {
    await setFirstHeadquarterOption();
    await setFirstGuidelineOption();
    await setFirstFormState();
  };

  return {
    headquartersOptions,
    guidelinesOptions,
    ownersOptions,
    setFirstHeadquarterOption, // Exporta la función para usarla externamente
    setFirstGuidelineOption,
    resetOptions,
  };
});
