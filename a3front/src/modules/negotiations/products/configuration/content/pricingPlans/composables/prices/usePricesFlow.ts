import { computed, ref } from 'vue';
import { usePricingPlanStore } from '../../stores/usePricingPlanStore';

export const usePricesFlow = () => {
  const store = usePricingPlanStore();

  const isSaving = ref(false);

  const variations = computed(() => store.state.sections.prices.rateVariations);

  const currentVariation = computed(() =>
    variations.value.find((v) => v.id === store.state.sections.prices.selectedRateVariationId)
  );

  const currentIndex = computed(() =>
    variations.value.findIndex((v) => v.id === store.state.sections.prices.selectedRateVariationId)
  );

  const allCompleted = computed(() => variations.value.every((v) => v.status === 'COMPLETED'));

  const completedCount = computed(
    () => variations.value.filter((v) => v.status === 'COMPLETED').length
  );

  const inProgressCount = computed(
    () => variations.value.filter((v) => v.status === 'IN_PROGRESS').length
  );

  const notStartedCount = computed(
    () => variations.value.filter((v) => v.status === 'NOT_STARTED').length
  );

  function getFirstPendingVariation() {
    return (
      variations.value.find((v) => v.status === 'NOT_STARTED') ||
      variations.value.find((v) => v.status === 'IN_PROGRESS') ||
      null
    );
  }

  const hasSingleVariation = computed(() => variations.value.length === 1);

  function getNextByIndex() {
    const index = currentIndex.value;
    if (index === -1) return null;

    return variations.value[index + 1] ?? null;
  }

  async function saveCurrentVariation() {
    const variation = currentVariation.value;
    if (!variation) return;

    isSaving.value = true;

    try {
      await store.saveCurrentRateVariation(variation);

      await store.loadRateVariations();
    } finally {
      isSaving.value = false;
    }
  }

  async function changeVariation(nextId: string) {
    const currentId = store.state.sections.prices.selectedRateVariationId;

    if (currentId === nextId) return;

    await saveCurrentVariation();
    store.selectVariation(nextId);
  }

  async function goToNextVariation(onFinish?: () => void) {
    await saveCurrentVariation();

    const next = getNextByIndex();

    if (allCompleted.value) {
      if (onFinish) {
        onFinish();
      }
      return;
    }

    if (next) {
      await store.selectVariation(next.id);
      return;
    }

    const pending = getFirstPendingVariation();

    if (pending) {
      await store.selectVariation(pending.id);
    }
  }

  async function saveAll() {
    const variation = currentVariation.value;
    if (!variation) return;

    if (hasSingleVariation.value) {
      if (variation.status !== 'COMPLETED') {
        await saveCurrentVariation();
      }
      return;
    }

    if (!allCompleted.value) return;
  }

  const canGoNext = computed(() => currentIndex.value < variations.value.length - 1);

  const canSaveAll = computed(() => {
    if (hasSingleVariation.value) return true;
    return allCompleted.value;
  });

  return {
    // acciones
    changeVariation,
    goToNextVariation,
    saveCurrentVariation,
    saveAll,
    getFirstPendingVariation,

    // estados
    canGoNext,
    canSaveAll,
    isSaving,

    // opcional (útil para UI)
    currentIndex,
    variations,
    allCompleted,
    completedCount,
    inProgressCount,
    notStartedCount,
  };
};
