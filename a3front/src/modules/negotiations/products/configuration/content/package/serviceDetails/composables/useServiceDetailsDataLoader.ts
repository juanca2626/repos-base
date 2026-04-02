import type { Ref, ComputedRef } from 'vue';
import type { FormState } from '@/modules/negotiations/products/configuration/interfaces/shared-service.interface';
import type { UseServiceDetailsFormProps } from '@/modules/negotiations/products/configuration/content/shared/interfaces/serviceDetails';
import { mapApiSchedulesToScheduleData } from '@/modules/negotiations/products/configuration/content/shared/utils/schedule-mapper.utils';
import { mapApiStatusToFormStatus } from '@/modules/negotiations/products/configuration/content/shared/utils/status-mapper.utils';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';

export const useServiceDetailsDataLoader = (
  serviceDetail: ComputedRef<any>,
  formState: Ref<FormState>,
  resetFormState: () => void,
  props: UseServiceDetailsFormProps
) => {
  const configurationStore = useConfigurationStore();

  const loadServiceDetailData = () => {
    if (!serviceDetail.value || !props.currentKey || !props.currentCode) {
      resetFormState();
      return;
    }

    const { content } = serviceDetail.value;

    // Validar que content existe
    if (!content) {
      console.warn('ServiceDetail no tiene content:', serviceDetail.value);
      resetFormState();
      return;
    }

    // Mapear basicInfo con validación
    if (content.basicInfo) {
      formState.value.serviceName = content.basicInfo.name || '';
      formState.value.subtype = content.basicInfo.subTypeCode;
      formState.value.profile = content.basicInfo.profileCode;
    }

    // Mapear logistics con validación (multiDays usa un solo valor: primer elemento del array)
    if (content.logistics) {
      formState.value.startPoint = content.logistics.startPointCodes?.[0];
      formState.value.endPoint = content.logistics.endPointCodes?.[0];
      formState.value.duration = content.logistics.duration || '00:00';
    }

    // Mapear status con validación
    if (content.status) {
      formState.value.status = mapApiStatusToFormStatus(content.status.state);
      formState.value.reason = content.status.reason || '';
      formState.value.showToClient = content.status.clientVisible ?? true;
    }

    // Mapear schedules y guardarlos en el store con validación
    if (content.operability && content.operability.schedules) {
      const mappedScheduleData = mapApiSchedulesToScheduleData(
        content.operability.schedules,
        content.operability.mode
      );

      configurationStore.setServiceDetailsSchedule(
        props.currentKey,
        props.currentCode,
        mappedScheduleData
      );
    }
  };

  return {
    loadServiceDetailData,
  };
};
