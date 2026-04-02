import { storeToRefs } from 'pinia';
import { useSupplierFormStore } from '@/modules/negotiations/supplier/register/store/supplierFormStore';

export function useSupplierFormStoreFacade() {
  const supplierFormStore = useSupplierFormStore();

  const {
    isLoadingForm,
    isFormEditMode,
    supplierLocationsUpdate,
    isEditFormTreasury,
    isEditFormAccounting,
    configSubClassification,
    subClassificationSupplierId,
  } = storeToRefs(supplierFormStore);

  const {
    formStateNegotiation,
    formStateTreasury,
    formStateAccounting,
    extraValidations,
    resetFormStateNegotiation,
    resetFormStateTreasury,
    resetFormStateAccounting,
    setIsLoadingForm,
    setIsFormEditMode,
    resetExtraValidations,
    resetExtraValidationByKey,
    applySupplierLocationsUpdate,
    resetSupplierLocationsUpdate,
    setIsEditFormTreasury,
    setIsEditFormAccounting,
    setConfigSubClassification,
  } = supplierFormStore;

  const resetFormData = () => {
    resetFormStateNegotiation();
    resetFormStateTreasury();
    resetFormStateAccounting();
    resetExtraValidations();
    resetSupplierLocationsUpdate();
  };

  return {
    isLoadingForm,
    isFormEditMode,
    formStateNegotiation,
    formStateTreasury,
    formStateAccounting,
    extraValidations,
    supplierLocationsUpdate,
    isEditFormTreasury,
    isEditFormAccounting,
    configSubClassification,
    subClassificationSupplierId,
    setIsLoadingForm,
    setIsFormEditMode,
    resetFormStateNegotiation,
    resetFormStateTreasury,
    resetFormStateAccounting,
    resetExtraValidations,
    resetFormData,
    resetExtraValidationByKey,
    applySupplierLocationsUpdate,
    setIsEditFormTreasury,
    setIsEditFormAccounting,
    setConfigSubClassification,
  };
}
