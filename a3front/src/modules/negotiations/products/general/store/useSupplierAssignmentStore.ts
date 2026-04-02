import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSupplierAssignmentStore = defineStore('supplierAssignmentStore', () => {
  const isSupplierAssignmentForm = ref<boolean>(true);

  const setIsSupplierAssignmentForm = (value: boolean) => {
    isSupplierAssignmentForm.value = value;
  };

  return {
    isSupplierAssignmentForm,
    setIsSupplierAssignmentForm,
  };
});
