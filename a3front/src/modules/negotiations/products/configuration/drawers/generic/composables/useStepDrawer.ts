import { computed, ref } from 'vue';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/general/interfaces/form';

export function useStepDrawer(emit: DrawerEmitTypeInterface) {
  const stepNumber = ref<number>(1);

  const cancelButtonText = computed(() => {
    return stepNumber.value === 1 ? 'Cancelar' : 'Atrás';
  });

  const nextButtonText = computed(() => {
    return stepNumber.value === 2 ? 'Guardar' : 'Siguiente';
  });

  const handleGoBack = (): void => {
    if (stepNumber.value > 1) {
      stepNumber.value -= 1;
    } else {
      emit('update:showDrawerForm', false);
    }
  };

  const handleGoNext = (): void => {
    stepNumber.value += 1;
  };

  const resetSteps = () => {
    stepNumber.value = 1;
  };

  return {
    stepNumber,
    cancelButtonText,
    nextButtonText,
    handleGoBack,
    handleGoNext,
    resetSteps,
  };
}
