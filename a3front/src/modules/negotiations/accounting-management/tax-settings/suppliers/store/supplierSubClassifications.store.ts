import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { useSupportModuleResources } from '@/modules/negotiations/composables/useSupportModuleResources';

export const useSupplierSubClassificationsStore = defineStore('supplierSubClassifications', () => {
  const supplierSubClassificationList = ref([]);
  const isLoading = ref(false);
  const isLoaded = ref(false);
  const { resources, fetchSupportResources } = useSupportModuleResources();

  const fetchServiceClassifications = async () => {
    if (isLoaded.value) return;
    isLoading.value = true;
    try {
      await fetchSupportResources('supplier_sub_classifications');
      isLoaded.value = true;
    } catch (error) {
      console.error('Error fetching supplier sub classifications:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(resources, (newResources) => {
    if (newResources && newResources.data && newResources.data.supplier_sub_classifications) {
      supplierSubClassificationList.value = newResources.data.supplier_sub_classifications;
    }
  });

  return {
    supplierSubClassificationList,
    isLoading,
    isLoaded,
    fetchServiceClassifications,
  };
});
