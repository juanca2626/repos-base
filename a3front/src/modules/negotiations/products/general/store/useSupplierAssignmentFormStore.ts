import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { SupplierForm } from '@/modules/negotiations/products/general/interfaces/form';

export const useSupplierAssignmentFormStore = defineStore('supplierAssignmentFormStore', () => {
  const allSuppliers = ref<SupplierForm[]>([]);
  const suppliersToAssign = ref<SupplierForm[]>([]);
  const assignedSuppliers = ref<SupplierForm[]>([]);
  const selectedSupplierKeys = ref<number[]>([]);

  const assignSupplier = (supplier: SupplierForm) => {
    assignedSuppliers.value.push(supplier);

    suppliersToAssign.value = suppliersToAssign.value.filter(
      (item) => item.supplierOriginalId !== supplier.supplierOriginalId
    );
  };

  const splitSuppliersBySelection = () => {
    const selectedSuppliers = new Set(selectedSupplierKeys.value);
    const toAssign: SupplierForm[] = [];
    const assigned: SupplierForm[] = [];

    for (const supplier of suppliersToAssign.value) {
      if (selectedSuppliers.has(supplier.supplierOriginalId)) {
        assigned.push(supplier);
      } else {
        toAssign.push(supplier);
      }
    }

    return {
      assigned,
      toAssign,
    };
  };

  const assignMultipleSuppliers = (toAssign: SupplierForm[], assigned: SupplierForm[]) => {
    assignedSuppliers.value.push(...assigned);
    suppliersToAssign.value = toAssign;
    selectedSupplierKeys.value = [];
  };

  const deleteAssignedSuppliers = (supplier: SupplierForm) => {
    const supplierItem = allSuppliers.value.find((item) => {
      return item.supplierOriginalId === supplier.supplierOriginalId;
    });

    suppliersToAssign.value.push(supplierItem ?? supplier);

    assignedSuppliers.value = assignedSuppliers.value.filter(
      (item) => item.supplierOriginalId !== supplier.supplierOriginalId
    );

    suppliersToAssign.value.sort((a, b) => a.supplierOriginalId - b.supplierOriginalId);
  };

  const assignAllSuppliers = () => {
    suppliersToAssign.value.forEach((item) => {
      assignedSuppliers.value.push(item);
    });

    suppliersToAssign.value = [];
  };

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
});
