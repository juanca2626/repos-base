import { useEntrancesStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useEntrances() {
  const entrancesStore = useEntrancesStore();
  const { isLoading, error, states, filters, pagination, getEntrances, entrance } =
    storeToRefs(entrancesStore);
  const { fetchAll, findService, save, changeStatus, reserve, download, send } = entrancesStore;

  return {
    isLoading,
    error,
    entrance,
    filters,
    pagination,
    states,
    entrances: getEntrances,
    fetchEntrances: fetchAll,
    fetchService: findService,
    saveEntrance: save,
    updateStatus: changeStatus,
    reserveEntrance: reserve,
    downloadEntrance: download,
    sendEntrance: send,
  };
}
