import { computed } from 'vue';
import { usePricingPlanStore } from '@/modules/negotiations/products/configuration/content/pricingPlans/stores/usePricingPlanStore';

export const useRateVariationSelector = () => {
  const store = usePricingPlanStore();

  const statePrice = computed(() => store.state.sections.prices);

  const variationCards = computed(() => statePrice.value.rateVariations);

  const selectedRateVariation = computed(() =>
    statePrice.value.rateVariations.find((v) => v.id === statePrice.value.selectedRateVariationId)
  );

  const selectVariation = (id: string) => {
    statePrice.value.selectedRateVariationId = id;
  };

  const hasVariations = computed(() => variationCards.value.length > 0);

  const selectedRateVariationId = computed(() => statePrice.value.selectedRateVariationId);

  return {
    variationCards,
    selectVariation,
    hasVariations,
    selectedRateVariationId,
    selectedRateVariation,
  };
};
