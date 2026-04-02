import { ref } from 'vue';
import type { FormState } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';
import { ServiceStatusForm } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';

const getInitialFormState = (): FormState => ({
  serviceName: '',
  subtype: undefined,
  profile: undefined,
  startPoint: undefined,
  endPoint: undefined,
  duration: '',
  measurementUnit: undefined,
  minCapacity: undefined,
  maxCapacity: undefined,
  includesChildren: false,
  includesInfants: false,
  status: ServiceStatusForm.ACTIVO,
  reason: '',
  showToClient: true,
  typeText: [],
  itinerary: '',
  menu: '',
  remarks: '',
});

export const useServiceDetailsFormState = () => {
  const formState = ref<FormState>(getInitialFormState());

  const resetFormState = () => {
    formState.value = getInitialFormState();
  };

  return {
    formState,
    resetFormState,
  };
};
