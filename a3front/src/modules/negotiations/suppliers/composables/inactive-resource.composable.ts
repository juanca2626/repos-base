import { storeToRefs } from 'pinia';
import { useStatusStore } from '../store/status-store';

export function useInactiveResource(resource: string) {
  const statusStore = useStatusStore();
  const { isLoading } = storeToRefs(statusStore);

  const inactive = async (id: number) => {
    await statusStore.setInactive(resource, id);
  };

  return {
    isLoading,
    inactive,
  };
}
