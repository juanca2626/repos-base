import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { fetchCancellationReasons } from '@ordercontrol/api';

interface CancellationReasonFromApi {
  _id: string;
  name: string;
  [key: string]: any;
}

interface CancellationReasonOption {
  value: string;
  label: string;
}

export const useCancellationReasonStore = defineStore('cancellationReasonStore', () => {
  const isLoading = ref(false);
  const cancellationReasons = ref<CancellationReasonOption[]>([]);
  const error = ref<string | null>(null);
  const getCancellationReasons = computed(() => cancellationReasons.value);

  /**
   * Fetches cancellation reasons from the API and maps them for select components.
   */
  const fetchAll = async () => {
    isLoading.value = true;
    error.value = null;
    try {
      const response = await fetchCancellationReasons();
      if (response && response.data) {
        cancellationReasons.value = response.data.map((reason: CancellationReasonFromApi) => ({
          value: reason._id,
          label: reason.name,
        }));
      } else {
        cancellationReasons.value = [];
      }
    } catch (e: any) {
      error.value = e.message || 'An unknown error occurred while fetching cancellation reasons.';
      cancellationReasons.value = [];
    } finally {
      isLoading.value = false;
    }
  };

  return {
    isLoading,
    getCancellationReasons,
    error,
    fetchAll,
  };
});
