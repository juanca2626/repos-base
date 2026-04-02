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
    subtype: [{ required: true, message: 'El subtipo es requerido', trigger: 'change' }],
    profile: [{ required: true, message: 'El perfil es requerido', trigger: 'change' }],
    startPoint: [
      {
        validator: (_rule: any, value: string | string[] | undefined) => {
          const valid =
            value != null && (Array.isArray(value) ? value.length > 0 : (value as string) !== '');
          return valid ? Promise.resolve() : Promise.reject('El punto de inicio es requerido');
        },
        trigger: 'change',
      },
    ],
    endPoint: [
      {
        validator: (_rule: any, value: string | string[] | undefined) => {
          const valid =
            value != null && (Array.isArray(value) ? value.length > 0 : (value as string) !== '');
          return valid ? Promise.resolve() : Promise.reject('El punto de fin es requerido');
        },
        trigger: 'change',
      },
    ],
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
  subtipoOptions?: ComputedRef<Array<{ label: string; value: any }>>,
  scheduleData?: ComputedRef<ScheduleData | null>
) => {
  const formRules = getFormRules(formState);

  // Determinar si el subtipo está disponible
  const hasSubtypeAvailable = computed(() => {
    if (!subtipoOptions) return false;
    return subtipoOptions.value.length > 0;
  });

  const getIsFormValid = computed(() => {
    const status = formState.value.status;
    const hasReason = !!formState.value.reason?.trim();
    const reasonRequired =
      status === ServiceStatusForm.SUSPENDIDO || status === ServiceStatusForm.INACTIVO;
    const reasonValid = !reasonRequired || hasReason;

    const hasStartPoint =
      formState.value.startPoint != null &&
      (Array.isArray(formState.value.startPoint)
        ? formState.value.startPoint.length > 0
        : formState.value.startPoint !== '');
    const hasEndPoint =
      formState.value.endPoint != null &&
      (Array.isArray(formState.value.endPoint)
        ? formState.value.endPoint.length > 0
        : formState.value.endPoint !== '');

    const isScheduleValid = scheduleData ? validateSchedules(scheduleData.value) : true;

    return (
      formState.value.serviceName.trim() !== '' &&
      (!hasSubtypeAvailable.value || formState.value.subtype !== undefined) &&
      formState.value.profile !== undefined &&
      hasStartPoint &&
      hasEndPoint &&
      formState.value.status !== undefined &&
      formState.value.showToClient !== undefined &&
      reasonValid &&
      isScheduleValid
    );
  });

  const completedFields = computed(() => {
    const hasStartPoint =
      formState.value.startPoint != null &&
      (Array.isArray(formState.value.startPoint)
        ? formState.value.startPoint.length > 0
        : !!formState.value.startPoint);
    const hasEndPoint =
      formState.value.endPoint != null &&
      (Array.isArray(formState.value.endPoint)
        ? formState.value.endPoint.length > 0
        : !!formState.value.endPoint);

    let count = 0;
    if (formState.value.serviceName) count++;
    // Solo contar subtipo si hay opciones disponibles
    if (hasSubtypeAvailable.value && formState.value.subtype) count++;
    if (formState.value.profile) count++;
    if (hasStartPoint) count++;
    if (hasEndPoint) count++;
    if (formState.value.status) count++;
    if (formState.value.showToClient !== null && formState.value.showToClient !== undefined)
      count++;
    return count;
  });

  const totalFields = computed(() => {
    // Si hay subtipo disponible, son 7 campos, si no, son 6
    return hasSubtypeAvailable.value ? 7 : 6;
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
