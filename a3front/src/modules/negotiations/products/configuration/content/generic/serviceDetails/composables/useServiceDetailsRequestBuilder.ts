import type { Ref, ComputedRef } from 'vue';
import type { FormState } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';
import type { ServiceDetailsRequest } from '../interfaces/service-details-request.interface';
import type { UseServiceDetailsFormProps } from '@/modules/negotiations/products/configuration/content/shared/interfaces/serviceDetails';
import { mapStatusToApiFormat } from '@/modules/negotiations/products/configuration/content/shared/utils/status-mapper.utils';

export const useServiceDetailsRequestBuilder = (
  serviceDetail: ComputedRef<any>,
  formState: Ref<FormState>,
  totalDuration: ComputedRef<string>,
  scheduleType: ComputedRef<number>,
  getApiSchedules: () => any[],
  getOperabilityFlags: () => { applyAllDay: boolean; singleTime: boolean },
  getIsFormValid: ComputedRef<boolean>,
  serviceSubTypes: ComputedRef<any[]>,
  props: UseServiceDetailsFormProps
) => {
  const buildServiceDetailsRequest = (): ServiceDetailsRequest => {
    const configId = serviceDetail.value?.id || null;
    const apiSchedules = getApiSchedules();
    const { applyAllDay: operabilityApplyAllDay, singleTime: operabilitySingleTime } =
      getOperabilityFlags();

    return {
      id: configId,
      groupingKeys: {
        operatingLocationKey: props.currentKey || '',
        supplierCategoryCode: props.currentCode || '',
      },
      content: {
        basicInfo: {
          name: formState.value.serviceName || '',
          subTypeCode: formState.value.subtype || '',
          profileCode: formState.value.profile || '',
        },
        logistics: {
          startPointCodes: Array.isArray(formState.value.startPoint)
            ? formState.value.startPoint.filter(Boolean)
            : formState.value.startPoint
              ? [formState.value.startPoint]
              : [],
          endPointCodes: Array.isArray(formState.value.endPoint)
            ? formState.value.endPoint.filter(Boolean)
            : formState.value.endPoint
              ? [formState.value.endPoint]
              : [],
          duration: totalDuration.value || '00:00',
        },
        operability: {
          mode: scheduleType.value === 1 ? 'ALL_DAYS' : 'CUSTOM',
          applyAllDay: operabilityApplyAllDay,
          singleTime: operabilitySingleTime,
          schedules: apiSchedules,
        },
        status: {
          state: mapStatusToApiFormat(formState.value.status),
          reason: formState.value.reason || '',
          clientVisible: formState.value.showToClient || false,
        },
      },
      completionStatus: getIsFormValid.value ? 'COMPLETED' : 'IN_PROGRESS',
    };
  };

  return {
    buildServiceDetailsRequest,
  };
};
