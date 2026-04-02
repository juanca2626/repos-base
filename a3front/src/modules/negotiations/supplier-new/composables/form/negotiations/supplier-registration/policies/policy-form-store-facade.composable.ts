import { storeToRefs } from 'pinia';
import { useSupplierPolicyFormStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/policies/supplier-policy-form.store';

export function usePolicyFormStoreFacade() {
  const supplierPolicyFormStore = useSupplierPolicyFormStore();

  const {
    reloadPolicyData,
    policyId,
    clonePolicyId,
    reloadHolidayCalendars,
    formMode,
    policyCloneResponse,
    clonedPolicyData,
  } = storeToRefs(supplierPolicyFormStore);

  const {
    formState,
    setFormState,
    resetFormState,
    setReloadPolicyData,
    setPolicyId,
    setClonePolicyId,
    setReloadHolidayCalendars,
    setFormMode,
    setPolicyCloneResponse,
    setClonedPolicyData,
    clearClonedPolicyData,
  } = supplierPolicyFormStore;

  return {
    formState,
    reloadPolicyData,
    policyId,
    clonePolicyId,
    reloadHolidayCalendars,
    formMode,
    policyCloneResponse,
    clonedPolicyData,
    setFormState,
    resetFormState,
    setReloadPolicyData,
    setPolicyId,
    setClonePolicyId,
    setReloadHolidayCalendars,
    setFormMode,
    setPolicyCloneResponse,
    setClonedPolicyData,
    clearClonedPolicyData,
  };
}
