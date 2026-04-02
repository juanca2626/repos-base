import { defineStore } from 'pinia';
import { reactive, ref } from 'vue';

import { useOptionsStore } from './options.store';

export const useDrawerStore = defineStore('drawerStore', () => {
  const optionsStore = useOptionsStore();

  //form.store.ts
  // const formStore = useFormStore();
  // const { formState } = formStore;

  const showDrawer = ref<boolean>(false);
  const editModalGuideline = ref<boolean>(false);
  const state = reactive<any>({
    showModal: false,
  });

  const handlerShowDrawer = async (show: boolean, edit: boolean = false) => {
    editModalGuideline.value = edit;
    if (!editModalGuideline.value) {
      await optionsStore.resetOptions();
    }
    showDrawer.value = show;
  };

  const showModal = () => {
    state.showModal = true;
  };

  return {
    // Properties
    showDrawer,
    state,
    editModalGuideline,
    // Actions
    handlerShowDrawer,
    showModal,
  };
});
