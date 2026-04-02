import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { ServiceDetailsLoaderFactory } from '../factories/serviceDetails/serviceDetailsLoaderFactory';

export const useServiceDetailsDataLoader = () => {
  const route = useRoute();
  const { isServiceTypeTrain, isServiceTypeMultiDays, isServiceTypeGeneral } =
    useSelectedServiceType();

  const isLoadingServiceDetails = ref(false);

  const loadServiceDetailsData = async () => {
    const productSupplierId = route.params.id as string;

    if (!productSupplierId) return;

    try {
      isLoadingServiceDetails.value = true;

      const loader = ServiceDetailsLoaderFactory.createLoader(
        isServiceTypeTrain,
        isServiceTypeMultiDays,
        isServiceTypeGeneral
      );

      await loader.loadServiceDetails(productSupplierId);
    } catch (error) {
      console.error('Error loading service details:', error);
    } finally {
      isLoadingServiceDetails.value = false;
    }
  };

  return {
    loadServiceDetailsData,
    isLoadingServiceDetails,
  };
};
