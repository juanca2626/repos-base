import { storeToRefs } from 'pinia';
import { useSupplierAssignmentStore } from '@/modules/negotiations/products/general/store/useSupplierAssignmentStore';

export function useSupplierAssignmentStoreFacade() {
  const supplierAssignmentStore = useSupplierAssignmentStore();

  const { setIsSupplierAssignmentForm } = supplierAssignmentStore;

  const { isSupplierAssignmentForm } = storeToRefs(supplierAssignmentStore);

  return {
    isSupplierAssignmentForm,
    setIsSupplierAssignmentForm,
  };
}
