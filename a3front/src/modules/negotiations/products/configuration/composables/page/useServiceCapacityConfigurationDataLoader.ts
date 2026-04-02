import { ref } from 'vue';
import { useRoute } from 'vue-router';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { CapacityConfigurationLoaderFactory } from '../factories/capacityConfiguration/capacityConfigurationLoaderFactory';

export const useServiceCapacityConfigurationDataLoader = () => {
  const route = useRoute();
  const { isServiceTypeTrain, isServiceTypeMultiDays, isServiceTypeGeneral } =
    useSelectedServiceType();

  const isLoadingCapacityConfiguration = ref(false);

  const loadServiceCapacityConfigurationData = async () => {
    const productSupplierId = route.params.id as string;
    if (!productSupplierId) return;

    try {
      isLoadingCapacityConfiguration.value = true;

      const loader = CapacityConfigurationLoaderFactory.createLoader(
        isServiceTypeTrain,
        isServiceTypeMultiDays,
        isServiceTypeGeneral
      );

      await loader.loadCapacityConfiguration(productSupplierId);
    } catch (error) {
      console.error('Error loading service capacity configuration:', error);
    } finally {
      isLoadingCapacityConfiguration.value = false;
    }
  };

  return {
    loadServiceCapacityConfigurationData,
    isLoadingCapacityConfiguration,
  };
};
