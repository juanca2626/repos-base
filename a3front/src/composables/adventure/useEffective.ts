import { useEffectiveStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useEffective() {
  const effectiveStore = useEffectiveStore();

  const { isLoading, rucs, cash, error, filters, states, pagination, getEffective } =
    storeToRefs(effectiveStore);

  const { fetchAll, updateStatus, updateStatusItem, saveDocuments, validateRuc } = effectiveStore;

  return {
    isLoading,
    error,
    filters,
    states,
    pagination,
    cash,
    rucs,
    effective: getEffective,
    fetchEffective: fetchAll,
    updateStatus,
    updateStatusItem,
    save: saveDocuments,
    validate: validateRuc,
  };
}
