import { storeToRefs } from 'pinia';
import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';

export function useSupplierClassificationStoreFacade() {
  const supplierClassificationStore = useSupplierClassificationStore();

  const {
    supplierClassificationId,
    supplierSubClassificationId,

    supplierClassifications,
    supplierSubClassifications,
    loadingForm,
    loadingButton,
    disabledButton,
  } = storeToRefs(supplierClassificationStore);

  const { setSupplierClassificationId, setSupplierSubClassificationId } =
    supplierClassificationStore;

  return {
    supplierClassificationId,
    supplierSubClassificationId,

    supplierClassifications,
    supplierSubClassifications,
    loadingForm,
    loadingButton,
    disabledButton,

    setSupplierClassificationId,
    setSupplierSubClassificationId,
  };
}
