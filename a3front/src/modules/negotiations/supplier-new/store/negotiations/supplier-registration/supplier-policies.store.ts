import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { PolicyViewTypeEnum } from '@/modules/negotiations/supplier-new/enums/policy-view-type.enum';

export const useSupplierPoliciesStore = defineStore('supplierPoliciesStore', () => {
  const sourceData = ref<any>([]);

  const pendingRequests = ref<number>(0);

  const startLoading = () => {
    return pendingRequests.value++;
  };
  const stopLoading = () => {
    return (pendingRequests.value = Math.max(pendingRequests.value - 1, 0));
  };

  const isLoading = computed(() => pendingRequests.value > 0);

  const showForm = ref<boolean>(false);
  const loadingButton = ref<boolean>(false);
  const reloadList = ref<boolean>(false);

  // controlar vistas en policy manager
  const policyViewType = ref<PolicyViewTypeEnum>(PolicyViewTypeEnum.INFORMATION_BASIC);

  const setReloadList = (value: boolean) => {
    reloadList.value = value;
  };

  const setPolicyViewType = (value: PolicyViewTypeEnum) => {
    policyViewType.value = value;
  };

  /**
   * Obtiene una política del sourceData por su ID
   * @param id - ID de la política (string _id de MongoDB)
   */
  const getPolicyById = (id: string | null) => {
    if (!id) return null;
    return sourceData.value.find((policy: any) => policy.id === id || policy._id === id) || null;
  };

  return {
    // Data
    sourceData,

    // // UI State
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
});
