import { computed } from 'vue';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { storeToRefs } from 'pinia';
import type { PriceState } from '../types/price.types';

export const useFrequencyOptions = (pricingState: PriceState) => {
  const trainStore = useTrainConfigurationStore();
  const navigationStore = useNavigationStore();

  const { currentKey, currentCode } = storeToRefs(navigationStore);

  const serviceDetail = computed(() =>
    trainStore.getServiceDetail(currentKey.value || '', currentCode.value || '')
  );

  const frequenciesCatalog = computed(() => serviceDetail.value?.content?.frequencies || []);

  const variations = computed(() => pricingState.rateVariations);
  const selectedId = computed(() => pricingState.selectedRateVariationId);

  const currentVariation = computed(() => variations.value.find((v) => v.id === selectedId.value));

  const getGroupId = (v: any) => v.parentVariationId ?? v.id;

  const usedFrequencies = computed(() => {
    const current = currentVariation.value;
    if (!current) return new Set<string>();

    const used = new Set<string>();

    const currentGroupId = getGroupId(current);

    variations.value.forEach((v) => {
      if (v.id === current.id) return;

      const groupId = getGroupId(v);

      if (groupId === currentGroupId) {
        v.frequencies.forEach((f) => used.add(f));
      }
    });

    return used;
  });

  const options = computed(() => {
    const current = currentVariation.value;
    if (!current) return [];

    return frequenciesCatalog.value.map((f: any) => {
      const isUsed = usedFrequencies.value.has(f.id);
      const isSelected = current.frequencies.includes(f.id);

      return {
        value: f.id,
        label: `${f.code} - ${f.fareType}`,
        disabled: isUsed && !isSelected,
      };
    });
  });

  return {
    options,
    frequencies: frequenciesCatalog,
  };
};
