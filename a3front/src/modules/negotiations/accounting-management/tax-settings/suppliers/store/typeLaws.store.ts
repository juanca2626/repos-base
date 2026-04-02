import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { useSupportModuleResources } from '@/modules/negotiations/composables/useSupportModuleResources';

export const useTypeLawsStore = defineStore('typeLaws', () => {
  const typeLawList = ref([]);
  const isLoading = ref(false);
  const isLoaded = ref(false);
  const { resources, fetchSupportResources } = useSupportModuleResources();

  const fetchTypeLaws = async () => {
    if (isLoaded.value) return;
    isLoading.value = true;
    try {
      await fetchSupportResources('type_laws');
      isLoaded.value = true;
    } catch (error) {
      console.error('Error fetching law list:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(resources, (newResources) => {
    if (newResources && newResources.data && newResources.data.type_laws) {
      typeLawList.value = newResources.data.type_laws;
    }
  });

  return {
    typeLawList,
    isLoading,
    isLoaded,
    fetchTypeLaws,
  };
});
