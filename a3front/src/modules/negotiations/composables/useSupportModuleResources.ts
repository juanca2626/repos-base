import { type Ref, ref } from 'vue';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { SupportResourceResponseInterface } from '@/modules/negotiations/interfaces/support-resource-response.interface';

export const useSupportModuleResources = () => {
  const resources: Ref<SupportResourceResponseInterface | null> = ref(null);
  const loading = ref<boolean>(false);
  const error = ref<Error | null>(null);

  const fetchSupportResources = async (resourceKey: string): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
      const response = await supportApi.get<SupportResourceResponseInterface[]>(
        'support/resources',
        {
          params: { 'keys[]': resourceKey },
        }
      );

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
    fetchSupportResources,
  };
};
