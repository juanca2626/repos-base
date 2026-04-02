import { storeToRefs } from 'pinia';
import { useSupplierGlobalStore } from '@/modules/negotiations/supplier-new/store/supplier-global.store';

export function useSupplierGlobalStoreFacade() {
  const supplierGlobalStore = useSupplierGlobalStore();

  const {
    isLoadingFormGeneral,
    isLoadingModuleGeneral,
    supplierViewType,
    supplierId,
    supplier,
    isEditModeState,
  } = storeToRefs(supplierGlobalStore);

  const { setIsLoadingFormGeneral, setIsLoadingModuleGeneral, setSupplierViewType } =
    supplierGlobalStore;

  return {
    isLoadingFormGeneral,
    isLoadingModuleGeneral,
    supplierViewType,
    supplierId,
    supplier,
    isEditModeState,

    setIsLoadingFormGeneral,
    setIsLoadingModuleGeneral,
    setSupplierViewType,
  };
}
