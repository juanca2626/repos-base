import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { SupplierSubClassificationsEnum } from '@/utils/supplierSubClassifications.enum';
import type { ViewDataMap } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

export function useDetailsSection() {
  const viewData: ViewDataMap = {
    [SupplierSubClassificationsEnum.TOURIST_TRANSPORT]: {
      configureAs: 'Operador turístico',
      subClassificationDescription: 'Transporte terrestre',
    },
    [SupplierSubClassificationsEnum.MUSEUMS]: {
      configureAs: 'MUS - Entradas',
      subClassificationDescription: 'MUS - Museos',
    },
  };

  const { formStateNegotiation, configSubClassification } = useSupplierFormStoreFacade();

  const getSubClassificationDescription = (): string => {
    return configSubClassification.value
      ? (viewData[configSubClassification.value]?.subClassificationDescription ?? 'Default')
      : 'Default';
  };

  const getConfigureAs = (): string => {
    return configSubClassification.value
      ? (viewData[configSubClassification.value]?.configureAs ?? 'DEF - Default')
      : 'DEF - Default';
  };

  return {
    formStateNegotiation,
    configSubClassification,
    getConfigureAs,
    getSubClassificationDescription,
  };
}
