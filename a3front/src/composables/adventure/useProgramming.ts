import { useProgrammingStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useProgramming() {
  const programmingStore = useProgrammingStore();

  const {
    isLoading,
    filters,
    error,
    pagination,
    getProgrammings,
    getStates,
    getTypes,
    programming,
  } = storeToRefs(programmingStore);

  const { fetchAll, deactivate, reset, send, save, update } = programmingStore;

  return {
    isLoading,
    filters,
    error,
    pagination,
    programming,
    fetchProgramming: fetchAll,
    programmings: getProgrammings,
    states: getStates,
    types: getTypes,
    deactivateProgramming: deactivate,
    resetProgramming: reset,
    saveProgramming: save,
    updateProgramming: update,
    sendOrder: send,
  };
}
