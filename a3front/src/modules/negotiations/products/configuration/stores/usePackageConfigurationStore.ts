import { defineStore } from 'pinia';
import { ref } from 'vue';
import type { MultiDaysServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type {
  ScheduleData,
  ScheduleKey,
} from '@/modules/negotiations/products/configuration/shared/types/types';
import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';

export const usePackageConfigurationStore = defineStore('packageConfigurationStore', () => {
  const serviceDetails = ref<Map<string, MultiDaysServiceDetailsResponse>>(new Map());
  const serviceDetailsSchedules = ref<Record<ScheduleKey, ScheduleData>>({});
  const capacityConfigurations = ref<CapacityConfigurationResponse[]>([]);
  const serviceContents = ref<any[]>([]);

  const getServiceDetail = (
    programDurationCode: string,
    operationalSeasonCode: string
  ): MultiDaysServiceDetailsResponse | null => {
    const key = `${programDurationCode}-${operationalSeasonCode}`;
    return serviceDetails.value.get(key) || null;
  };

  const getServiceDetailId = (currentKey: string, currentCode: string): string | null => {
    const serviceDetail = getServiceDetail(currentKey, currentCode);
    return serviceDetail?.id || null;
  };

  const setServiceDetails = (details: MultiDaysServiceDetailsResponse[]) => {
    serviceDetails.value.clear();
    if (details && Array.isArray(details)) {
      details.forEach((detail) => {
        const key = `${detail.groupingKeys.programDurationCode}-${detail.groupingKeys.operationalSeasonCode}`;
        serviceDetails.value.set(key, detail);
      });
    }
  };

  const getScheduleKey = (
    programDurationCode: string,
    operationalSeasonCode: string
  ): ScheduleKey => {
    return `${programDurationCode}-${operationalSeasonCode}`;
  };

  const getServiceDetailsSchedule = (
    programDurationCode: string,
    operationalSeasonCode: string
  ): ScheduleData | null => {
    const key = getScheduleKey(programDurationCode, operationalSeasonCode);
    return serviceDetailsSchedules.value[key] || null;
  };

  const getCapacityConfiguration = (
    currentKey: string,
    currentCode: string
  ): CapacityConfigurationResponse | null => {
    return (
      capacityConfigurations.value.find(
        (config) =>
          config.groupingKeys.programDurationCode === currentKey &&
          config.groupingKeys.operationalSeasonCode === currentCode
      ) || null
    );
  };

  const getServiceContent = (
    programDurationCode: string,
    operationalSeasonCode: string
  ): any | null => {
    return (
      serviceContents.value.find(
        (content) =>
          content.groupingKeys.programDurationCode === programDurationCode &&
          content.groupingKeys.operationalSeasonCode === operationalSeasonCode
      ) || null
    );
  };

  const setServiceDetailsSchedule = (
    programDurationCode: string,
    operationalSeasonCode: string,
    scheduleData: ScheduleData
  ) => {
    const key = getScheduleKey(programDurationCode, operationalSeasonCode);
    serviceDetailsSchedules.value[key] = {
      scheduleType: scheduleData.scheduleType,
      scheduleGeneral: JSON.parse(JSON.stringify(scheduleData.scheduleGeneral)), // Deep copy
      schedule: JSON.parse(JSON.stringify(scheduleData.schedule)), // Deep copy
    };
  };

  const updateServiceDetail = (detail: MultiDaysServiceDetailsResponse) => {
    const key = `${detail.groupingKeys.programDurationCode}-${detail.groupingKeys.operationalSeasonCode}`;
    serviceDetails.value.set(key, detail);
  };

  const setServiceContents = (key: string, code: string, contents: any[]) => {
    const index = serviceContents.value.findIndex(
      (c) =>
        c.groupingKeys.programDurationCode === key && c.groupingKeys.operationalSeasonCode === code
    );
    if (index !== -1) {
      serviceContents.value[index] = {
        groupingKeys: {
          programDurationCode: key,
          operationalSeasonCode: code,
        },
        ...contents,
      };
    } else {
      serviceContents.value.push({
        groupingKeys: {
          programDurationCode: key,
          operationalSeasonCode: code,
        },
        ...contents,
      });
    }
  };

  const updateServiceContent = (content: any) => {
    const index = serviceContents.value.findIndex(
      (c) =>
        c.groupingKeys.programDurationCode === content.groupingKeys.programDurationCode &&
        c.groupingKeys.operationalSeasonCode === content.groupingKeys.operationalSeasonCode
    );
    if (index !== -1) {
      serviceContents.value[index] = content;
    } else {
      serviceContents.value.push(content);
    }
  };

  const setCapacityConfigurations = (configs: CapacityConfigurationResponse[]) => {
    capacityConfigurations.value = [...configs];
  };

  const updateCapacityConfiguration = (config: CapacityConfigurationResponse) => {
    const index = capacityConfigurations.value.findIndex(
      (c) =>
        c.groupingKeys.programDurationCode === config.groupingKeys.programDurationCode &&
        c.groupingKeys.operationalSeasonCode === config.groupingKeys.operationalSeasonCode
    );

    if (index !== -1) {
      // Actualizar existente
      capacityConfigurations.value[index] = config;
    } else {
      // Agregar nuevo
      capacityConfigurations.value.push(config);
    }
  };

  const clearData = () => {
    serviceDetails.value.clear();
    serviceDetailsSchedules.value = {};
    capacityConfigurations.value = [];
  };

  return {
    serviceDetails,
    serviceDetailsSchedules,
    serviceContents,

    getServiceDetail,
    setServiceDetails,
    getServiceDetailsSchedule,
    setServiceDetailsSchedule,
    updateServiceDetail,
    getServiceDetailId,
    setCapacityConfigurations,
    updateCapacityConfiguration,
    getCapacityConfiguration,

    setServiceContents,
    updateServiceContent,
    getServiceContent,

    clearData,
  };
});
