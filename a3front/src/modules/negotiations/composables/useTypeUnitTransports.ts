import { ref, type Ref } from 'vue';
import type { TypeUnitTransportsResponseInterface } from '@/modules/negotiations/interfaces/type-unit-transports-response.interface';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';

export const useTypeUnitTransports = () => {
  const resources: Ref<TypeUnitTransportsResponseInterface | null> = ref(null);
  const loading = ref<boolean>(false);
  const error = ref<Error | null>(null);

  const fetchTypeUnitTransports = async (): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
      const response = await supportApi.get<TypeUnitTransportsResponseInterface[]>('units/all');
      resources.value = response.data;
    } catch (e) {
      error.value = e instanceof Error ? e : new Error('An unknown error occurred');
      console.error('Error fetching support resources:', e);
    } finally {
      loading.value = false;
    }
  };

  return {
    resources,
    loading,
    error,
    fetchTypeUnitTransports,
  };
};
