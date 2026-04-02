// import { ProviderProfileType } from './../interfaces/provider-profile-type.interface';
import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import { mapToSelectOptions } from '@operations/shared/utils/formUtils';

import {
  type ProviderContractType,
  type ProviderProfileType,
  type Headquarter,
  type BlockingReason,
} from '../interfaces';
import {
  fetchProviderContractTypes,
  fetchProviderProfileTypes,
  fetchHeadquarters,
  fetchBlockingReasons,
} from '@/modules/operations/modules/blackout-calendar/api/blackoutCalendarApi';

export const useDataStore = defineStore('dataStore', () => {
  // Estado
  const providerContractTypes = ref<ProviderContractType[]>([]);
  const providerProfileTypes = ref<ProviderProfileType[]>([]);
  const headquarters = ref<Headquarter[]>([]);
  const blockingReasons = ref<BlockingReason[]>([]);

  // Acción para obtener los tipos de contrato
  const getProviderContractTypes = async () => {
    try {
      const response = await fetchProviderContractTypes();
      console.log('🚀 ~ getProviderContractTypes ~ response:', response);

      if (response && response.data) {
        providerContractTypes.value = response.data;
      } else {
        console.warn('No se recibieron datos de providerContractTypes.');
        providerContractTypes.value = [];
      }
    } catch (error) {
      console.error('Error obteniendo tipos de contrato:', error);
      providerContractTypes.value = []; // Opcional: Evitar valores no definidos en el estado
    }
  };

  const getProviderProfileTypes = async () => {
    try {
      const response = await fetchProviderProfileTypes();
      console.log('🚀 ~ getProviderProfileTypes ~ response:', response);

      if (response && response.data) {
        providerProfileTypes.value = response.data;
      } else {
        console.warn('No se recibieron datos de providerProfileTypes.');
        providerProfileTypes.value = [];
      }
    } catch (error) {
      console.error('Error obteniendo tipos de perfiles:', error);
      providerProfileTypes.value = []; // Opcional: Evitar valores no definidos en el estado
    }
  };

  const getHeadquarters = async () => {
    try {
      const response = await fetchHeadquarters();
      console.log('🚀 ~ getHeadquarters ~ response:', response);

      if (response && response.data) {
        headquarters.value = response.data;
      } else {
        console.warn('No se recibieron datos de headquarters.');
        headquarters.value = [];
      }
    } catch (error) {
      console.error('Error obteniendo locaciones:', error);
      headquarters.value = []; // Opcional: Evitar valores no definidos en el estado
    }
  };

  const getBlockingReasons = async () => {
    try {
      const response = await fetchBlockingReasons();
      console.log('🚀 ~ getBlockingReasons ~ response:', response);

      if (response && response.data) {
        blockingReasons.value = response.data;
      } else {
        console.warn('No se recibieron datos de blockingReasons.');
        blockingReasons.value = [];
      }
    } catch (error) {
      console.error('Error obteniendo razones de bloqueo:', error);
      blockingReasons.value = []; // Opcional: Evitar valores no definidos en el estado
    }
  };

  // Getter para transformar datos en opciones de selección
  const providerContractTypesOptions = computed(() =>
    mapToSelectOptions(providerContractTypes.value, 'Todos (Planta & Freelance)')
  );

  const providerProfileTypesOptions = computed(() =>
    mapToSelectOptions(providerProfileTypes.value, 'Todos los perfiles')
  );

  const headquartersOptions = computed(() =>
    headquarters.value.map(({ code, description }) => ({ value: code, label: description }))
  );

  const blockingReasonsOptions = computed(() =>
    blockingReasons.value.map(({ _id, iso, description, guide_type }) => ({
      value: _id,
      label: `${iso} - ${description}`,
      disabled: guide_type === 'A', // ✅ Deshabilitar opciones con guide_type 'A'
    }))
  );

  return {
    // State
    providerContractTypes,
    providerProfileTypes,
    headquarters,
    blockingReasons,

    // Getters
    providerContractTypesOptions,
    providerProfileTypesOptions,
    headquartersOptions,
    blockingReasonsOptions,

    // Actions
    getProviderContractTypes,
    getProviderProfileTypes,
    getHeadquarters,
    getBlockingReasons,
  };
});
