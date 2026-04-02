import { defineStore } from 'pinia';
import { ref } from 'vue';
import type {
  ServiceDetailsResponse,
  Operability,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type {
  ScheduleData,
  ScheduleKey,
} from '@/modules/negotiations/products/configuration/shared/types/types';
import type { CapacityConfigurationResponse } from '@/modules/negotiations/products/configuration/content/shared/interfaces/configuration/capacity-configuration.interface';

export const useGenericConfigurationStore = defineStore('genericConfigurationStore', () => {
  const serviceDetails = ref<Map<string, ServiceDetailsResponse>>(new Map());
  const serviceDetailsSchedules = ref<Record<ScheduleKey, ScheduleData>>({});
  const capacityConfigurations = ref<CapacityConfigurationResponse[]>([]);
  const serviceContents = ref<any[]>([]);

  const getScheduleKey = (currentKey: string, currentCode: string): ScheduleKey => {
    return `${currentKey}-${currentCode}`;
  };

  const setServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string,
    scheduleData: ScheduleData
  ) => {
    const key = getScheduleKey(currentKey, currentCode);
    serviceDetailsSchedules.value[key] = {
      scheduleType: scheduleData.scheduleType,
      scheduleGeneral: JSON.parse(JSON.stringify(scheduleData.scheduleGeneral)),
      schedule: JSON.parse(JSON.stringify(scheduleData.schedule)),
    };
  };

  const getServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string
  ): ScheduleData | null => {
    const key = getScheduleKey(currentKey, currentCode);
    return serviceDetailsSchedules.value[key] || null;
  };

  const setServiceDetails = (details: ServiceDetailsResponse[]) => {
    serviceDetails.value.clear();
    if (details && Array.isArray(details)) {
      details.forEach((detail) => {
        if (detail && detail.groupingKeys) {
          const key = `${detail.groupingKeys.operatingLocationKey}-${detail.groupingKeys.supplierCategoryCode}`;
          serviceDetails.value.set(key, detail);
        }
      });
    }
  };

  const updateServiceDetail = (detail: ServiceDetailsResponse) => {
    const key = `${detail.groupingKeys.operatingLocationKey}-${detail.groupingKeys.supplierCategoryCode}`;
    serviceDetails.value.set(key, detail);
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

  const getServiceDetailOperability = (
    currentKey: string,
    currentCode: string
  ): Operability | null => {
    const serviceDetail = getServiceDetail(currentKey, currentCode);
    return serviceDetail?.content.operability || null;
  };

  const getCapacityConfiguration = (
    currentKey: string,
    currentCode: string
  ): CapacityConfigurationResponse | null => {
    return (
      capacityConfigurations.value.find(
        (config) =>
          config.groupingKeys.operatingLocationKey === currentKey &&
          config.groupingKeys.supplierCategoryCode === currentCode
      ) || null
    );
  };

  const getServiceContent = (key: string, code: string): any | null => {
    return (
      serviceContents.value.find(
        (content) =>
          content.groupingKeys.operatingLocationKey === key &&
          content.groupingKeys.supplierCategoryCode === code
      ) || null
    );
  };

  const updateCapacityConfiguration = (config: CapacityConfigurationResponse) => {
    const index = capacityConfigurations.value.findIndex(
      (c) =>
        c.groupingKeys.operatingLocationKey === config.groupingKeys.operatingLocationKey &&
        c.groupingKeys.supplierCategoryCode === config.groupingKeys.supplierCategoryCode
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

  const setServiceContents = (key: string, code: string, contents: any[]) => {
    const index = serviceContents.value.findIndex(
      (c) =>
        c.groupingKeys.operatingLocationKey === key && c.groupingKeys.supplierCategoryCode === code
    );
    if (index !== -1) {
      serviceContents.value[index] = {
        groupingKeys: {
          operatingLocationKey: key,
          supplierCategoryCode: code,
        },
        ...contents,
      };
    } else {
      serviceContents.value.push({
        groupingKeys: {
          operatingLocationKey: key,
          supplierCategoryCode: code,
        },
        ...contents,
      });
    }
  };

  const updateServiceContent = (content: any) => {
    const index = serviceContents.value.findIndex(
      (c) =>
        c.groupingKeys.operatingLocationKey === content.groupingKeys.operatingLocationKey &&
        c.groupingKeys.supplierCategoryCode === content.groupingKeys.supplierCategoryCode
    );
    if (index !== -1) {
      serviceContents.value[index] = content;
    } else {
      serviceContents.value.push(content);
    }
  };

  const clearData = () => {
    serviceDetailsSchedules.value = {};
    serviceDetails.value.clear();
    capacityConfigurations.value = [];
    serviceContents.value = [];
  };

  return {
    serviceDetails,
    serviceDetailsSchedules,
    capacityConfigurations,
    serviceContents,

    // Métodos específicos de Generic
    setServiceDetailsSchedule,
    getServiceDetailsSchedule,
    setServiceDetails,
    updateServiceDetail,
    getServiceDetail,
    getServiceDetailId,
    getCapacityConfiguration,
    updateCapacityConfiguration,
    setCapacityConfigurations,

    getServiceDetailOperability,
    setServiceContents,
    updateServiceContent,
    getServiceContent,

    clearData,
  };
});
