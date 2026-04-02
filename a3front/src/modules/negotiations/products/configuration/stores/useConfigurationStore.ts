import { ref, computed } from 'vue';
import { defineStore } from 'pinia';
import { useGenericConfigurationStore } from './useGenericConfigurationStore';
import { useTrainConfigurationStore } from './useTrainConfigurationStore';
import { usePackageConfigurationStore } from './usePackageConfigurationStore';

import type {
  ServiceDetailsResponse,
  MultiDaysServiceDetailsResponse,
} from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';

import type { ScheduleData } from '@/modules/negotiations/products/configuration/shared/types/types';

import type {
  ConfigurationItem,
  FetchConfigurationModel,
} from '@/modules/negotiations/products/configuration/domain/configuration/models/fetchConfiguration.model';

import type {
  ProductSummary,
  SupplierSummary,
} from '@/modules/negotiations/products/configuration/interfaces/product-supplier-summary.interface';

import { ServiceTypeEnum } from '@/modules/negotiations/products/general/enums/service-type.enum';
import { ProductSupplierTypeEnum } from '@/modules/negotiations/products/general/enums/product-supplier-type.enum';

import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';
import type { Role, ServiceType } from '../types/index';

import { fetchConfigurationUseCase } from '@/modules/negotiations/products/configuration/application/configuration/fetchConfiguration.useCase';
import { fetchSummaryUseCase } from '@/modules/negotiations/products/configuration/application/configuration/fetchSummary.useCase';
import { fetchContentUseCase } from '@/modules/negotiations/products/configuration/application/content/fetchContent.useCase';
import { saveConfigurationUseCase } from '@/modules/negotiations/products/configuration/application/configuration/saveConfiguration.useCase';
import { loadExternalHolidaysUseCase } from '@/modules/negotiations/products/configuration/application/holidays/loadExternalHolidays.useCase';

import type { HolidaysResponse } from '@/modules/negotiations/products/configuration/domain/holidays/models/holidaysResponse.model';
import type { LoadExternalHolidaysParams } from '@/modules/negotiations/products/configuration/domain/holidays/types/loadExternalHolidaysParams.types';

export const useConfigurationStore = defineStore('configurationStore', () => {
  const genericStore = useGenericConfigurationStore();
  const trainStore = useTrainConfigurationStore();
  const packageStore = usePackageConfigurationStore();

  const productSupplierId = ref<string | null>(null);
  const productSummary = ref<ProductSummary | null>(null);
  const supplierSummary = ref<SupplierSummary | null>(null);
  const selectedServiceTypeId = ref<number | null>(null);
  const items = ref<ConfigurationItem[]>([]);
  const loading = ref(false);

  const holidays = ref<HolidaysResponse | null>(null);
  const loadingHoliday = ref(false);

  const isServiceType = (type: ServiceTypeEnum) =>
    computed(() => selectedServiceTypeId.value === type);

  const isServiceTypeTrain = isServiceType(ServiceTypeEnum.TRAIN_TICKET);
  const isServiceTypeMultiDays = isServiceType(ServiceTypeEnum.MULTIDAYS);

  const excludedServiceTypesForGeneral = new Set<ServiceTypeEnum>([
    ServiceTypeEnum.TRAIN_TICKET,
    ServiceTypeEnum.MULTIDAYS,
  ]);

  const isServiceTypeGeneral = computed(() => {
    if (selectedServiceTypeId.value == null) return false;
    return !excludedServiceTypesForGeneral.has(selectedServiceTypeId.value);
  });

  const supplierTypeMap: Partial<Record<ServiceTypeEnum, ProductSupplierTypeEnum>> = {
    [ServiceTypeEnum.TRAIN_TICKET]: ProductSupplierTypeEnum.TRAIN,
    [ServiceTypeEnum.MULTIDAYS]: ProductSupplierTypeEnum.PACKAGE,
  };

  const productSupplierType = computed(() => {
    if (selectedServiceTypeId.value == null) {
      return ProductSupplierTypeEnum.GENERIC;
    }

    return (
      supplierTypeMap[selectedServiceTypeId.value as ServiceTypeEnum] ??
      ProductSupplierTypeEnum.GENERIC
    );
  });

  // resolver de stores
  const storeResolver: Partial<Record<ServiceTypeEnum, any>> = {
    [ServiceTypeEnum.TRAIN_TICKET]: trainStore,
    [ServiceTypeEnum.MULTIDAYS]: packageStore,
  };

  const activeStore = computed(() => {
    if (!selectedServiceTypeId.value) return genericStore;

    return storeResolver[selectedServiceTypeId.value as ServiceTypeEnum] ?? genericStore;
  });

  function setConfiguration(model: FetchConfigurationModel) {
    items.value = model.items;
    loading.value = false;
  }

  function setProductSupplier(id: string) {
    productSupplierId.value = id;
  }

  const setServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string,
    scheduleData: ScheduleData
  ) => {
    if ('setServiceDetailsSchedule' in genericStore && isServiceTypeGeneral.value) {
      genericStore.setServiceDetailsSchedule(currentKey, currentCode, scheduleData);
    }

    if ('setServiceDetailsSchedule' in packageStore && isServiceTypeMultiDays.value) {
      packageStore.setServiceDetailsSchedule(currentKey, currentCode, scheduleData);
    }
  };

  const setServiceDetails = (details: ServiceDetailsResponse[]) => {
    if ('setServiceDetails' in genericStore) {
      genericStore.setServiceDetails(details);
    }

    if ('setServiceDetails' in trainStore) {
      trainStore.setServiceDetails(details);
    }
  };

  const updateServiceDetail = (
    detail: ServiceDetailsResponse | MultiDaysServiceDetailsResponse
  ) => {
    if ('updateServiceDetail' in genericStore && isServiceTypeGeneral.value) {
      genericStore.updateServiceDetail(detail as ServiceDetailsResponse);
    }

    if ('updateServiceDetail' in packageStore && isServiceTypeMultiDays.value) {
      packageStore.updateServiceDetail(detail as MultiDaysServiceDetailsResponse);
    }
  };

  const setTrainServiceDetailsSchedule = (
    currentKey: string,
    currentCode: string,
    trainScheduleData: any
  ) => {
    if ('setTrainServiceDetailsSchedule' in trainStore && isServiceTypeTrain.value) {
      trainStore.setTrainServiceDetailsSchedule(currentKey, currentCode, trainScheduleData);
    }
  };

  const setCapacityConfigurations = (configs: any[]) => {
    if ('setCapacityConfigurations' in genericStore && isServiceTypeGeneral.value) {
      genericStore.setCapacityConfigurations(configs);
    }
  };

  const loadConfiguration = async (
    productSupplierIdValue?: string,
    productSupplierTypeValue?: ProductSupplierTypeEnum,
    isSetConfiguration: boolean = true
  ) => {
    try {
      loading.value = true;

      const configuration = await fetchConfigurationUseCase(
        productSupplierIdValue ? productSupplierIdValue : (productSupplierId.value ?? ''),
        productSupplierTypeValue ? productSupplierTypeValue : productSupplierType.value
      );

      if (isSetConfiguration) {
        setConfiguration(configuration);
      }

      return configuration;
    } catch (error) {
      console.error('Error al cargar la configuración del servicio:', error);
    } finally {
      loading.value = false;
    }
  };

  const saveConfiguration = async (
    serviceType: ServiceType,
    productSupplierId: string,
    configuration: Configuration
  ) => {
    try {
      loading.value = true;

      const data = await saveConfigurationUseCase({
        serviceType: serviceType,
        productSupplierId: productSupplierId,
        configuration,
      });

      return data;
    } catch (error) {
      console.error('Error al guardar la configuración del servicio:', error);
    } finally {
      loading.value = false;
    }
  };

  const loadSummary = async () => {
    if (!productSupplierId.value) return;

    try {
      loading.value = true;

      const summary = await fetchSummaryUseCase(productSupplierId.value);

      if (summary) {
        productSummary.value = summary.product;
        supplierSummary.value = summary.supplier;
        selectedServiceTypeId.value = summary.product.serviceType.originalId;
      }
    } catch (error) {
      console.error('Error al cargar el resumen del servicio:', error);
    } finally {
      loading.value = false;
    }
  };

  const loadServiceContent = async (
    role: Role,
    currentKey?: string | null,
    currentCode?: string | null
  ): Promise<void> => {
    try {
      loading.value = true;

      if (!productSupplierId.value) return;

      const key = currentKey ?? '';
      const code = currentCode ?? '';

      if (!key || !code) return;

      const store = activeStore.value;

      const serviceDetailId = store.getServiceDetailId?.(key, code);

      if (!serviceDetailId) return;

      const content = await fetchContentUseCase(
        productSupplierType.value,
        role,
        productSupplierId.value,
        serviceDetailId
      );

      store.setServiceContents?.(key, code, content as any);
    } catch (error) {
      console.error('Error al cargar el contenido del servicio:', error);
    } finally {
      loading.value = false;
    }
  };

  const loadExternalHolidays = async (params: LoadExternalHolidaysParams) => {
    loadingHoliday.value = true;
    try {
      const response = await loadExternalHolidaysUseCase(params);
      holidays.value = response;
      return response;
    } catch (error) {
      console.error('Error loading holidays', error);
    } finally {
      loadingHoliday.value = false;
    }
  };

  function clear() {
    items.value = [];
    loading.value = false;
    productSupplierId.value = null;
    productSummary.value = null;
    supplierSummary.value = null;
    selectedServiceTypeId.value = null;

    activeStore.value?.clearData();
  }

  return {
    productSupplierId,
    productSummary,
    supplierSummary,
    selectedServiceTypeId,
    isServiceTypeTrain,
    isServiceTypeMultiDays,
    isServiceTypeGeneral,
    productSupplierType,
    items,
    loading,
    holidays,
    setConfiguration,
    setProductSupplier,

    setServiceDetails,
    setServiceDetailsSchedule,
    setTrainServiceDetailsSchedule,
    setCapacityConfigurations,

    updateServiceDetail,

    loadConfiguration,
    saveConfiguration,
    loadSummary,
    loadServiceContent,
    loadExternalHolidays,
    loadingHoliday,

    clear,
  };
});
