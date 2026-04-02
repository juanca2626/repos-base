import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useSupplierTabViewStore = defineStore('supplierTabViewStore', () => {
  const tabOptionCollaborator = ref<string>('negotiations');
  const setTabOptionCollaborator = (value: string) => {
    tabOptionCollaborator.value = value;
  };

  return {
    tabOptionCollaborator,
    setTabOptionCollaborator,
  };
});
