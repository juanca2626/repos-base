import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { PersistenceOptions } from 'pinia-plugin-persistedstate';
import { ServiceTypeEnum } from '@/modules/negotiations/products/general/enums/service-type.enum';

export const useSelectedServiceTypeStore = defineStore(
  'selectedServiceTypeStore',
  () => {
    //inicializar null cuando se integren todos los tipos y elimine create-shared-service
    const selectedServiceTypeId = ref<number | null>(ServiceTypeEnum.FOOD);

    const setSelectedServiceTypeId = (id: number | null) => {
      selectedServiceTypeId.value = id;
    };

    return {
      selectedServiceTypeId,
      setSelectedServiceTypeId,
    };
  },
  {
    persist: {
      storage: sessionStorage,
      // storage: localStorage, //cambiar a localStorage cuando se integren todos los tipos
      pick: ['selectedServiceTypeId'],
    } as PersistenceOptions,
  }
);
