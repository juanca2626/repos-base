import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { storeToRefs } from 'pinia';
import { useConfigurationStore } from '../../stores/useConfigurationStore';
import { useSelectedServiceTypeStore } from '@/modules/negotiations/products/general/store/useSelectedServiceTypeStore';

export const useProductSupplierSummaryLoader = () => {
  const route = useRoute();
  const configurationStore = useConfigurationStore();
  const selectedServiceTypeStore = useSelectedServiceTypeStore();
  const isLoadingSummary = ref(false);
  const { selectedServiceTypeId } = storeToRefs(configurationStore);

  const loadSummaryData = async () => {
    const productSupplierId = route.params.id as string;

    if (!productSupplierId) return;

    try {
      isLoadingSummary.value = true;

      await configurationStore.loadSummary();

      if (selectedServiceTypeId.value) {
        selectedServiceTypeStore.setSelectedServiceTypeId(selectedServiceTypeId.value);
      }
    } catch (error) {
      console.error('Error loading summary data:', error);
    } finally {
      isLoadingSummary.value = false;
    }
  };

  return {
    loadSummaryData,
    isLoadingSummary,
  };
};
