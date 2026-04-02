import { storeToRefs } from 'pinia';
import { useSupplierPoliciesStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-policies.store';

export function usePolicyStoreFacade() {
  const supplierPoliciesStore = useSupplierPoliciesStore();

  const { sourceData, isLoading, showForm, loadingButton, reloadList, policyViewType } =
    storeToRefs(supplierPoliciesStore);

  const { setReloadList, setPolicyViewType, startLoading, stopLoading, getPolicyById } =
    supplierPoliciesStore;

  return {
    sourceData,
    isLoading,
    showForm,
    loadingButton,
    reloadList,
    policyViewType,

    setReloadList,
    setPolicyViewType,
    startLoading,
    stopLoading,
    getPolicyById,
  };
}
