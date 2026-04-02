import { storeToRefs } from 'pinia';
import { useSupplierAssignmentFormStore } from '@/modules/negotiations/products/general/store/useSupplierAssignmentFormStore';

export function useSupplierAssignmentFormStoreFacade() {
  const supplierAssignmentFormStore = useSupplierAssignmentFormStore();

  const {
    assignSupplier,
    deleteAssignedSuppliers,
    assignAllSuppliers,
    assignMultipleSuppliers,
    splitSuppliersBySelection,
  } = supplierAssignmentFormStore;

  const { allSuppliers, suppliersToAssign, assignedSuppliers, selectedSupplierKeys } = storeToRefs(
    supplierAssignmentFormStore
  );

  return {
    allSuppliers,
    suppliersToAssign,
    assignedSuppliers,
    selectedSupplierKeys,
    assignSupplier,
    deleteAssignedSuppliers,
    assignAllSuppliers,
    assignMultipleSuppliers,
    splitSuppliersBySelection,
  };
}
