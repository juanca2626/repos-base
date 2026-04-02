import { ref, computed, onMounted, watch, toValue, type MaybeRefOrGetter } from 'vue';
import { getStepsByType } from '../flow/stepRegistry';
import { usePricingPlanStore } from '../stores/usePricingPlanStore';
import type { Step } from '../types/stepRegistry.interface';
import type { ServiceType } from '@/modules/negotiations/products/configuration/types/service.type';
import { getNextStepKey, getStepIndexByKey } from '../flow/stepNavigation';
import { usePricesFlow } from './prices/usePricesFlow';

export function usePricingPlanFlow(serviceTypeSource: MaybeRefOrGetter<ServiceType>) {
  const stepRef = ref();
  const ratePlanId = ref<string | null>(null);

  const steps = computed<Step[]>(() => getStepsByType(toValue(serviceTypeSource)));

  const currentStepIndex = ref(0);
  const isLoading = ref(false);
  const storePricingPlan = usePricingPlanStore();
  const isInitialized = ref(false);

  const { state, setStepErrors, clearStepErrors } = storePricingPlan;
  const pricesFlow = usePricesFlow();

  onMounted(async () => {
    await storePricingPlan.init(toValue(serviceTypeSource));
    resolveInitialStep();

    if (storePricingPlan.shouldLoadRateVariations()) {
      await storePricingPlan.loadRateVariations();
    }

    isInitialized.value = true;
  });

  watch(
    () => state.entityId,
    (value) => {
      ratePlanId.value = value ?? null;
    },
    { immediate: true }
  );

  const stepsWithState = computed(() =>
    steps.value.map((step) => ({
      ...step,
      completed: step.isComplete(state.sections[step.key]),
    }))
  );

  const currentStep = computed(() =>
    isInitialized.value ? stepsWithState.value[currentStepIndex.value] : null
  );

  const stepComponent = computed(() => currentStep.value?.component);

  const currentSection = computed(() => {
    if (!currentStep.value) return null;
    return state.sections[currentStep.value.key];
  });

  const errors = computed(() => {
    if (!currentStep.value) return null;
    return state.errors[currentStep.value.key];
  });

  const isPricesStep = computed(() => currentStep.value?.key === 'prices');

  const isStepValid = computed(() => {
    const step = currentStep.value;

    if (!step) return false;

    if (isPricesStep.value) {
      return true;
    }

    const result = step.validator(state.sections[step.key], toValue(serviceTypeSource));

    return result.valid;
  });

  const nextLabel = computed(() => {
    if (!isPricesStep.value) return 'Siguiente';

    return pricesFlow.allCompleted.value ? 'Guardar datos' : 'Siguiente';
  });

  async function next() {
    if (!isInitialized.value) return;

    const step = currentStep.value;

    if (!step) return;

    if (isPricesStep.value) {
      isLoading.value = true;

      try {
        await pricesFlow.goToNextVariation(() => {
          if (pricesFlow.allCompleted.value) {
            const nextKey = getNextStepKey(steps.value, step.key);

            if (nextKey) {
              currentStepIndex.value = getStepIndexByKey(steps.value, nextKey);
            }
          }
        });
      } finally {
        isLoading.value = false;
      }

      return;
    }

    const result = step.validator(state.sections[step.key], toValue(serviceTypeSource));

    if (!result.valid) {
      setStepErrors(step.key, result.errors);
      return;
    }

    clearStepErrors(step.key);

    isLoading.value = true;

    await step.action(state);

    const nextKey = getNextStepKey(steps.value, step.key);

    if (nextKey) {
      currentStepIndex.value = getStepIndexByKey(steps.value, nextKey);
    }

    isLoading.value = false;
  }

  function back() {
    if (currentStepIndex.value > 0) {
      currentStepIndex.value--;
    }
  }

  function resolveInitialStep() {
    const firstIncomplete = steps.value.find((step) => !step.isComplete(state.sections[step.key]));

    if (!firstIncomplete) return;

    currentStepIndex.value = getStepIndexByKey(steps.value, firstIncomplete.key);
  }

  return {
    stepRef,
    ratePlanId,
    steps,
    currentStepIndex,
    currentStep,
    stepComponent,
    currentSection,
    errors,
    state,
    next,
    back,
    isLoading,
    isStepValid,
    nextLabel,
  };
}
