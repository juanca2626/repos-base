import { computed } from 'vue';
import { useCommercialInformationCruiseComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/cruises/commercial-information-cruise.composable';

export function useAdditionalInformationComposable() {
  // 📌 Reutilizamos el formState del composable global
  const { formState } = useCommercialInformationCruiseComposable();

  const additionalInfo = computed({
    get: () => formState.additionalInformation,
    set: (val) => {
      formState.additionalInformation = val;
    },
  });

  return {
    formState, // si quieres acceder a todo
    additionalInfo, // si solo quieres el campo
  };
}
