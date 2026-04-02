import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSupplierLayoutStore = defineStore('supplierLayoutStore', () => {
  const supplierSubClassification = ref<number | null>(null);

  const setSupplierSubClassification = (value: number) => {
    supplierSubClassification.value = value;
  };

  return {
    supplierSubClassification,
    setSupplierSubClassification,
  };
});
