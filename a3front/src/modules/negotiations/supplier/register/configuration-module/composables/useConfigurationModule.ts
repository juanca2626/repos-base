import { useRoute } from 'vue-router';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { SupplierSubClassificationsEnum } from '@/utils/supplierSubClassifications.enum';

export function useConfigurationModule() {
  const route = useRoute();

  const { configSubClassification, setConfigSubClassification } = useSupplierFormStoreFacade();

  const getRouteSubClassificationId = (): number => {
    return Number(route.params.idClassification);
  };

  const isTransportSubClassification = (): boolean => {
    return configSubClassification.value === SupplierSubClassificationsEnum.TOURIST_TRANSPORT;
  };

  const isMuseumsSubClassification = (): boolean => {
    return configSubClassification.value === SupplierSubClassificationsEnum.MUSEUMS;
  };

  return {
    getRouteSubClassificationId,
    isTransportSubClassification,
    isMuseumsSubClassification,
    setConfigSubClassification,
  };
}
