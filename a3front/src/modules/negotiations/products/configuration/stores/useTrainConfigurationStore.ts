import { defineStore } from 'pinia';
import { ref } from 'vue';
import type {
  TrainScheduleData,
  ScheduleKey,
} from '@/modules/negotiations/products/configuration/shared/types/types';
import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

export const useTrainConfigurationStore = defineStore('trainConfigurationStore', () => {
  const trainServiceDetailsSchedules = ref<Record<ScheduleKey, TrainScheduleData>>({});
  const serviceDetails = ref<Map<string, ServiceDetailsResponse>>(new Map());
  const capacityConfigurations = ref<CapacityConfigurationResponse[]>([]);
  const serviceContents = ref<any[]>([]);

  const getScheduleKey = (currentKey: string, currentCode: string): ScheduleKey => {
    return `${currentKey}-${currentCode}`;
  };

  const setTrainServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string,
    trainScheduleData: TrainScheduleData
  ) => {
    const key = getScheduleKey(currentKey, currentCode);
    trainServiceDetailsSchedules.value[key] = {
      schedules: JSON.parse(JSON.stringify(trainScheduleData.schedules)), // Deep copy
      validityRows: JSON.parse(JSON.stringify(trainScheduleData.validityRows)), // Deep copy
    };
  };

  const getTrainServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string
  ): TrainScheduleData | null => {
    const key = getScheduleKey(currentKey, currentCode);
    return trainServiceDetailsSchedules.value[key] || null;
  };

  const setServiceDetails = (details: ServiceDetailsResponse[]) => {
    serviceDetails.value.clear();
    if (details && Array.isArray(details)) {
      details.forEach((detail) => {
        if (detail && detail.groupingKeys) {
          const key = `${detail.groupingKeys.operatingLocationKey}-${detail.groupingKeys.trainTypeCode}`;
          serviceDetails.value.set(key, detail);
        }
      });
    }
  };

  const updateServiceDetail = (detail: ServiceDetailsResponse) => {
    const key = `${detail.groupingKeys.operatingLocationKey}-${detail.groupingKeys.trainTypeCode}`;
    serviceDetails.value.set(key, detail);
  };

  const setServiceContents = (key: string, code: string, contents: any[]) => {
    const index = serviceContents.value.findIndex(
      (c) => c.groupingKeys.operatingLocationKey === key && c.groupingKeys.trainTypeCode === code
    );
    if (index !== -1) {
      serviceContents.value[index] = {
        groupingKeys: {
          operatingLocationKey: key,
          trainTypeCode: code,
        },
        ...contents,
      };
    } else {
      serviceContents.value.push({
        groupingKeys: {
          operatingLocationKey: key,
          trainTypeCode: code,
        },
        ...contents,
      });
    }
  };

  const updateServiceContent = (content: any) => {
    const index = serviceContents.value.findIndex(
      (c) =>
        c.groupingKeys.operatingLocationKey === content.groupingKeys.operatingLocationKey &&
        c.groupingKeys.trainTypeCode === content.groupingKeys.trainTypeCode
    );

    if (index !== -1) {
      serviceContents.value[index] = content;
    } else {
      serviceContents.value.push(content);
    }
  };

  const getServiceContent = (key: string, code: string): any | null => {
    return (
      serviceContents.value.find(
        (content) =>
          content.groupingKeys.operatingLocationKey === key &&
          content.groupingKeys.trainTypeCode === code
      ) || null
    );
  };

  const getServiceDetail = (
    currentKey: string,
    currentCode: string
  ): ServiceDetailsResponse | null => {
    const key = `${currentKey}-${currentCode}`;
    return serviceDetails.value.get(key) || null;
  };

  const getServiceDetailId = (currentKey: string, currentCode: string): string | null => {
    const serviceDetail = getServiceDetail(currentKey, currentCode);
    return serviceDetail?.id || null;
  };

  const getCapacityConfiguration = (
    currentKey: string,
    currentCode: string
  ): CapacityConfigurationResponse | null => {
    return (
      capacityConfigurations.value.find(
        (config) =>
          config.groupingKeys.operatingLocationKey === currentKey &&
          config.groupingKeys.trainTypeCode === currentCode
      ) || null
    );
  };

  const updateCapacityConfiguration = (config: CapacityConfigurationResponse) => {
    const index = capacityConfigurations.value.findIndex(
      (c) =>
        c.groupingKeys.operatingLocationKey === config.groupingKeys.operatingLocationKey &&
        c.groupingKeys.trainTypeCode === config.groupingKeys.trainTypeCode
    );

    if (index !== -1) {
      // Actualizar existente
      capacityConfigurations.value[index] = config;
    } else {
      // Agregar nuevo
      capacityConfigurations.value.push(config);
    }
  };

  const setCapacityConfigurations = (configs: CapacityConfigurationResponse[]) => {
    capacityConfigurations.value = [...configs];
  };

  const clearData = () => {
    trainServiceDetailsSchedules.value = {};
    serviceDetails.value.clear();
    capacityConfigurations.value = [];
    serviceContents.value = [];
  };

  return {
    // Propiedades específicas de Train,
    serviceDetails,
    capacityConfigurations,
    serviceContents,

    setTrainServiceDetailsSchedule,
    getTrainServiceDetailsSchedule,
    setServiceDetails,
    updateServiceDetail,
    getServiceDetail,
    getServiceDetailId,
    getCapacityConfiguration,
    updateCapacityConfiguration,
    setCapacityConfigurations,

    setServiceContents,
    updateServiceContent,
    getServiceContent,

    clearData,
  };
});
