import { ref } from 'vue';

export const useSeriesProgramsLayout = () => {
  const showDrawer = ref(false);
  const handlerShowDrawer = (val: boolean) => (showDrawer.value = val);

  return { showDrawer, handlerShowDrawer };
};
