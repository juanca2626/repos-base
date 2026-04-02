import { defineStore } from 'pinia';
import { ref, nextTick } from 'vue';

export const useDrawerStore = defineStore('drawer', () => {
  const isOpen = ref(false);

  const openDrawer = async () => {
    isOpen.value = true;
    await nextTick(); //
  };

  const closeDrawer = async () => {
    isOpen.value = false;
    await nextTick(); //
  };

  return { isOpen, openDrawer, closeDrawer };
});
