import { computed } from 'vue';
import { storeToRefs } from 'pinia';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export const useBasicStep = () => {
  const supportResourcesStore = useSupportResourcesStore();
  const { segmentations, markets, clients, days } = storeToRefs(supportResourcesStore);

  const segmentationOptions = computed(() =>
    segmentations.value.map((segmentation) => ({
      value: segmentation.code,
      label: segmentation.name,
    }))
  );

  const marketsOptions = computed(() =>
    markets.value.map((market) => ({
      value: market.code,
      label: market.name,
    }))
  );

  const clientsOptions = computed(() =>
    clients.value.map((client) => ({
      value: client.code,
      label: client.name,
    }))
  );

  const daysOptions = computed(() =>
    days.value.map((day) => ({
      value: day.code,
      name: day.name,
      label: day.label,
    }))
  );

  return {
    segmentationOptions,
    marketsOptions,
    clientsOptions,
    daysOptions,
  };
};
