import { computed, type Ref, type ComputedRef } from 'vue';
import type { FormInstance } from 'ant-design-vue';
import type { FormState } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';
import { ServiceStatusForm } from '@/modules/negotiations/products/configuration/content/shared/enums/service-status.enum';
import {
  validateSchedules,
  type ScheduleData,
} from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-validation.utils';

export const getFormRules = (formState: Ref<FormState>) => {
  return computed(() => ({
    serviceName: [
      { required: true, message: 'El nombre del servicio es requerido', trigger: 'blur' },
    ],
    profile: [{ required: true, message: 'El perfil es requerido', trigger: 'change' }],
    startPoint: [{ required: true, message: 'El punto de inicio es requerido', trigger: 'change' }],
    endPoint: [{ required: true, message: 'El punto de fin es requerido', trigger: 'change' }],
    status: [{ required: true, message: 'El estado es requerido', trigger: 'change' }],
    reason: [
      {
        validator: (_rule: any, value: string) => {
          const status = formState.value.status;
          if (status === ServiceStatusForm.SUSPENDIDO || status === ServiceStatusForm.INACTIVO) {
            if (!value || !value.trim()) {
              return Promise.reject(
                'El motivo es requerido cuando el estado es Suspendido o Inactivo'
              );
            }
          }
          return Promise.resolve();
        },
        trigger: 'blur',
      },
    ],
    showToClient: [{ required: true, message: 'Este campo es requerido', trigger: 'change' }],
  }));
};

export const useServiceDetailsFormValidation = (
  formState: Ref<FormState>,
  formRef: Ref<FormInstance | undefined>,
  scheduleData?: ComputedRef<ScheduleData | null>
) => {
  const formRules = getFormRules(formState);

  const getIsFormValid = computed(() => {
    const status = formState.value.status;
    const hasReason = !!formState.value.reason?.trim();
    const reasonRequired =
      status === ServiceStatusForm.SUSPENDIDO || status === ServiceStatusForm.INACTIVO;
    const reasonValid = !reasonRequired || hasReason;

    const isScheduleValid = scheduleData ? validateSchedules(scheduleData.value) : true;

    return (
      formState.value.serviceName.trim() !== '' &&
      formState.value.profile !== undefined &&
      formState.value.startPoint !== undefined &&
      formState.value.endPoint !== undefined &&
      formState.value.status !== undefined &&
      formState.value.showToClient !== undefined &&
      reasonValid &&
      isScheduleValid
    );
  });

  const completedFields = computed(() => {
    let count = 0;
    if (formState.value.serviceName) count++;
    if (formState.value.profile) count++;
    if (formState.value.startPoint) count++;
    if (formState.value.endPoint) count++;
    if (formState.value.status) count++;
    if (formState.value.showToClient !== null && formState.value.showToClient !== undefined)
      count++;
    return count;
  });

  const totalFields = computed(() => {
    return 6;
  });

  const validateForm = async (): Promise<void> => {
    await formRef.value?.validate();
  };

  return {
    formRules,
    getIsFormValid,
    completedFields,
    totalFields,
    validateForm,
  };
};
