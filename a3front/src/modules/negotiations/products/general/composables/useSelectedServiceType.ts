import { storeToRefs } from 'pinia';
import { computed } from 'vue';
import { useSelectedServiceTypeStore } from '@/modules/negotiations/products/general/store/useSelectedServiceTypeStore';
import { ServiceTypeEnum } from '@/modules/negotiations/products/general/enums/service-type.enum';
import { ProductSupplierTypeEnum } from '@/modules/negotiations/products/general/enums/product-supplier-type.enum';

export function useSelectedServiceType() {
  const selectedServiceTypeStore = useSelectedServiceTypeStore();

  const { setSelectedServiceTypeId } = selectedServiceTypeStore;

  const { selectedServiceTypeId } = storeToRefs(selectedServiceTypeStore);

  const excludedServiceTypesForGeneral = [ServiceTypeEnum.TRAIN_TICKET, ServiceTypeEnum.MULTIDAYS];

  const isServiceTypeTrain = computed(() => {
    return selectedServiceTypeId.value === ServiceTypeEnum.TRAIN_TICKET;
  });

  const isServiceTypeMultiDays = computed(() => {
    return selectedServiceTypeId.value === ServiceTypeEnum.MULTIDAYS;
  });

  const isServiceTypeGeneral = computed(() => {
    if (!selectedServiceTypeId.value) return false;

    return !excludedServiceTypesForGeneral.includes(selectedServiceTypeId.value);
  });

  const productSupplierType = computed(() => {
    if (isServiceTypeTrain.value) return ProductSupplierTypeEnum.TRAIN;

    if (isServiceTypeMultiDays.value) return ProductSupplierTypeEnum.PACKAGE;

    return ProductSupplierTypeEnum.GENERIC;
  });

  return {
    selectedServiceTypeId,
    isServiceTypeTrain,
    isServiceTypeMultiDays,
    isServiceTypeGeneral,
    productSupplierType,
    setSelectedServiceTypeId,
  };
}
