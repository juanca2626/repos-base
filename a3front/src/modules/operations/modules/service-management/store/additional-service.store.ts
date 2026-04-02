import { defineStore } from 'pinia';
import { ref } from 'vue';
import { useDataStore } from './data.store';

export const useAdditionalServiceStore = defineStore('additionalService', () => {
  const dataStore = useDataStore();

  const item = ref<any>(null);
  const is_required_carrier = ref(false);
  const is_required_guide = ref(false);

  const setFlagsFromService = (service: any) => {
    is_required_carrier.value =
      typeof service.requires_carrier === 'boolean' ? service.requires_carrier : false;

    is_required_guide.value =
      typeof service.requires_guide === 'boolean' ? service.requires_guide : false;
  };

  const setItem = async (service: any) => {
    item.value = service;
    setFlagsFromService(service);

    const result = await dataStore.getAdditionals(service.id);
    return JSON.parse(JSON.stringify(result)); // devolver copia limpia
  };

  const clearItem = () => {
    item.value = null;
    is_required_carrier.value = false;
    is_required_guide.value = false;
  };

  return {
    item,
    is_required_carrier,
    is_required_guide,
    setItem,
    clearItem,
  };
});
