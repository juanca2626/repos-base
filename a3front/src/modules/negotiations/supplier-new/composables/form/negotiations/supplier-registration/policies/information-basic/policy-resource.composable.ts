import { computed, reactive, ref, watch } from 'vue';
import { handleError } from '@/modules/negotiations/api/responseApi';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { mapItemsToOptions } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
import { usePolicyStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/policy-store-facade.composable';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import { SegmentationEnum } from '@/modules/negotiations/supplier-new/enums/supplier-registration/policies/segmentation.enum';
import type {
  ClientResponse,
  SelectOptionsLoading,
  SupplierPolicyForm,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

import {
  useSupplierPoliciesQuery,
  mapAppliesToToSelectFormat,
  mapPolicySegmentationToSelectFormat,
} from '@/modules/negotiations/supplier-new/composables/form/negotiations/optimized-queries/supplier-policies.query';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import { mapClientsToOptions } from '@/modules/negotiations/supplier-new/helpers/supplier-registration/policies/policy-form-helper';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';

export function usePolicyResourceComposable(formState: SupplierPolicyForm) {
  const selectOptionsLoading = reactive<SelectOptionsLoading>({
    markets: false,
    clients: false,
    holidayCalendars: false,
    serviceTypes: false,
    seasons: false,
  });

  const businessGroups = ref<SelectOption[]>([]);
  const businessGroupsWithLimits = ref<any[]>([]); // Almacena appliesTo completo con personLimit
  const segmentations = ref<SelectOption[]>([]);
  const markets = ref<SelectOption[]>([]);
  const allMarkets = ref<any[]>([]);
  const allClients = ref<ClientResponse[]>([]);
  const clients = ref<SelectOption[]>([]);
  const holidayCalendars = ref<SelectOption[]>([]);
  const serviceTypes = ref<SelectOption[]>([]);
  const allServiceTypes = ref<any[]>([]);
  const seasons = ref<SelectOption[]>([]);
  const allSeasons = ref<any[]>([]);

  const { fetchHolidayCalendars } = useSupplierResourceService;

  const { startLoading, stopLoading } = usePolicyStoreFacade();

  const { supplierId } = useSupplierGlobalComposable();

  const { data: policiesData, refetch: refetchPolicies } = useSupplierPoliciesQuery('default', {
    enabled: computed(() => true),
  });

  // Función para asegurar que todas las segmentaciones requeridas estén presentes
  const ensureRequiredSegmentations = (options: SelectOption[]): SelectOption[] => {
    const requiredSegmentations = [{ value: SegmentationEnum.SEASONS, label: 'Temporadas' }];

    const result = [...options];

    requiredSegmentations.forEach((required) => {
      const exists = result.some((opt) => opt.value === required.value);
      if (!exists) {
        result.push(required);
      }
    });

    return result;
  };

  const fetchMainResources = async () => {
    try {
      startLoading();

      if (!supplierId.value) {
        return;
      }

      // Solo hacer refetch si los datos no están disponibles
      if (!policiesData.value) {
        await refetchPolicies();
      }

      // NO cargar serviceTypes aquí - se cargarán cuando se seleccione SERVICE_TYPES
      // usando fetchServiceTypes() que llama al endpoint correcto: directSupportApi.get('service-types')

      if (policiesData.value) {
        businessGroups.value = mapItemsToOptions(
          mapAppliesToToSelectFormat(policiesData.value.data.appliesTo)
        );
        const mappedSegmentations = mapItemsToOptions(
          mapPolicySegmentationToSelectFormat(policiesData.value.data.policySegmentation)
        );
        // Asegurar que SEASONS esté disponible en las opciones
        segmentations.value = ensureRequiredSegmentations(mappedSegmentations);
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      stopLoading();
    }
  };

  const fetchSpecificationResources = async () => {
    // Esta función ahora es manejada por los queries reactivos
    // Se mantiene por compatibilidad pero no hace nada
    return Promise.resolve();
  };

  const applyFetchHolidayCalendar = () => {
    const { dateFrom, dateTo, policySegmentationIds } = formState;

    return dateFrom && dateTo && policySegmentationIds.includes(SegmentationEnum.EVENTS);
  };

  const getHolidayCalendars = async () => {
    if (!applyFetchHolidayCalendar()) return;

    try {
      holidayCalendars.value = [];
      selectOptionsLoading.holidayCalendars = true;

      const { data } = await fetchHolidayCalendars(formState.dateFrom, formState.dateTo);
      holidayCalendars.value = mapItemsToOptions(data.calendar);
    } catch (error: any) {
      handleError(error);
    } finally {
      selectOptionsLoading.holidayCalendars = false;
    }
  };

  // Computed map de opciones de especificación para reactividad
  // Incluye tanto keys numéricos (enum) como strings (códigos de backend)
  const specificationOptionsMap = computed<Record<number | string, SelectOption[]>>(() => ({
    // Keys numéricos (para compatibilidad con enum)
    [SegmentationEnum.MARKETS]: markets.value,
    [SegmentationEnum.CLIENTS]: clients.value,
    [SegmentationEnum.EVENTS]: holidayCalendars.value,
    [SegmentationEnum.SERVICE_TYPES]: serviceTypes.value,
    [SegmentationEnum.SEASONS]: seasons.value,
    // Keys string (códigos del backend)
    markets: markets.value,
    clients: clients.value,
    events: holidayCalendars.value,
    serviceTypes: serviceTypes.value,
    'service-types': serviceTypes.value,
    seasons: seasons.value,
  }));

  const getSpecificationData = (segmentationId: number | string): SelectOption[] => {
    const options = specificationOptionsMap.value[segmentationId] || [];
    return [...options];
  };

  // Función para cargar markets
  const fetchMarkets = async () => {
    try {
      selectOptionsLoading.markets = true;

      const response = await useSupplierService.showMarketsPolicies();

      if (response?.success && response.data) {
        const mappedMarkets = response.data.map((item: any) => ({
          id: item.code,
          code: item.code,
          name: item.name,
        }));
        allMarkets.value = mappedMarkets;
        markets.value = mapItemsToOptions(mappedMarkets);
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      selectOptionsLoading.markets = false;
    }
  };

  // Función para cargar clients
  const fetchClients = async () => {
    try {
      selectOptionsLoading.clients = true;

      const response = await useSupplierService.showClientsPolicies();

      if (response?.success && response.data) {
        const mappedClients = response.data.map((item: any) => ({
          id: item.code,
          code: item.code,
          name: item.name,
        }));
        allClients.value = mappedClients;
        clients.value = mapClientsToOptions(mappedClients);
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      selectOptionsLoading.clients = false;
    }
  };

  // Función para cargar service types
  const fetchServiceTypes = async () => {
    try {
      selectOptionsLoading.serviceTypes = true;

      const response = await useSupplierService.showServiceTypesPolicies();

      if (response?.success && response.data) {
        const mappedServiceTypes = response.data.map((item: any) => ({
          id: item.code,
          code: item.code,
          name: item.name,
        }));
        allServiceTypes.value = mappedServiceTypes;
        serviceTypes.value = mapItemsToOptions(mappedServiceTypes);
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      selectOptionsLoading.serviceTypes = false;
    }
  };

  // Función para cargar seasons (temporadas)
  const fetchSeasons = async () => {
    try {
      selectOptionsLoading.seasons = true;

      const response = await useSupplierService.showSeasonsPolicies();

      if (response?.success && response.data) {
        const mappedSeasons = response.data.map((item: any) => ({
          id: item.code,
          code: item.code,
          name: item.name,
        }));
        allSeasons.value = mappedSeasons;
        seasons.value = mapItemsToOptions(mappedSeasons);
      }
    } catch (error: any) {
      handleError(error);
    } finally {
      selectOptionsLoading.seasons = false;
    }
  };

  watch(
    policiesData,
    (newData) => {
      if (newData) {
        const mappedAppliesTo = mapAppliesToToSelectFormat(newData.data.appliesTo);
        businessGroupsWithLimits.value = mappedAppliesTo;
        businessGroups.value = mapItemsToOptions(mappedAppliesTo);
        const mappedSegmentations = mapItemsToOptions(
          mapPolicySegmentationToSelectFormat(newData.data.policySegmentation)
        );
        // Asegurar que SEASONS esté disponible en las opciones
        segmentations.value = ensureRequiredSegmentations(mappedSegmentations);
      }
    },
    { immediate: true }
  );

  return {
    selectOptionsLoading,
    businessGroups,
    businessGroupsWithLimits,
    segmentations,
    markets,
    allMarkets,
    allClients,
    allServiceTypes,
    allSeasons,
    clients,
    holidayCalendars,
    serviceTypes,
    seasons,
    specificationOptionsMap,
    fetchMainResources,
    fetchSpecificationResources,
    getHolidayCalendars,
    getSpecificationData,
    fetchMarkets,
    fetchClients,
    fetchServiceTypes,
    fetchSeasons,
  };
}
