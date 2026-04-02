import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { useTypeUnitTransports } from '@/modules/negotiations/composables/useTypeUnitTransports';

export const useTypeUnitsTransportsStore = defineStore('typeUnitsTransports', () => {
  const typeUnitsTransportsList = ref([]);
  const isLoading = ref(false);
  const isLoaded = ref(false);
  const { resources, fetchTypeUnitTransports } = useTypeUnitTransports();

  const fetchTypeUnitsTransports = async () => {
    if (isLoaded.value) return;
    isLoading.value = true;
    try {
      await fetchTypeUnitTransports();
      isLoaded.value = true;
    } catch (error) {
      console.error('Error fetching type units transports:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(resources, (newResources) => {
    if (newResources && newResources.data && newResources.data) {
      typeUnitsTransportsList.value = newResources.data;
    }
  });

  return {
    typeUnitsTransportsList,
    isLoading,
    isLoaded,
    fetchTypeUnitsTransports,
  };
});
