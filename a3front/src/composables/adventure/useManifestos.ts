import { useManifestosStore } from '@/stores/adventure';
import { storeToRefs } from 'pinia';

export function useManifestos() {
  const manifestosStore = useManifestosStore();

  const { isLoading, error, filters, pagination, getManifestos, total_paxs } =
    storeToRefs(manifestosStore);

  const { fetchAll } = manifestosStore;

  return {
    isLoading,
    error,
    filters,
    pagination,
    manifestos: getManifestos,
    fetchManifestos: fetchAll,
    total_paxs,
  };
}
