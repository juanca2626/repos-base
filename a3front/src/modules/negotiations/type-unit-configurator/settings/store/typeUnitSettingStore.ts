import { defineStore } from 'pinia';
import { ref } from 'vue';

export const useTypeUnitSettingStore = defineStore('typeUnitSettingStore', () => {
  const showActionButtons = ref<boolean>(false);
  const showDrawerForm = ref<boolean>(false);

  const setShowActionButtons = (value: boolean) => {
    showActionButtons.value = value;
  };

  const setShowDrawerForm = (value: boolean): void => {
    showDrawerForm.value = value;
  };

  return {
    showDrawerForm,
    showActionButtons,
    setShowActionButtons,
    setShowDrawerForm,
  };
});
