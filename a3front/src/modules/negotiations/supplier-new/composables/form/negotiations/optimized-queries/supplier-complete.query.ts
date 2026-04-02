import { computed, unref } from 'vue';
import { useQuery, type UseQueryOptions } from '@tanstack/vue-query';
import type { Ref } from 'vue';

import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { useSupplierResourceService } from '@/modules/negotiations/supplier-new/service/supplier-resources.service';
import { adaptClassificationsCatalog } from '@/modules/negotiations/supplier-new/adapters/supplier-classification.adapter';

// ─── Tipos internos normalizados ─────────────────────────────────────────────

export interface SupplierClassificationNormalized {
  typeCode: string;
  name: string;
  group: string;
}

export interface SupplierSubClassificationNormalized {
  subtypeCode: string;
  name: string;
  parentTypeCode: string;
}

export interface NormalizedClassifications {
  classifications: SupplierClassificationNormalized[];
  subClassifications: SupplierSubClassificationNormalized[];
}

// ─── Tipos de la API anidada (nueva estructura) ───────────────────────────────

interface ApiSubtype {
  name: string;
  subtypeCode: string;
}

interface ApiType {
  _id?: string;
  type: string;
  typeCode: string;
  subtypes: ApiSubtype[];
}

interface ApiClassificationGroup {
  _id?: string;
  group: string;
  types: ApiType[];
}

export interface SupplierCompleteDataParams {
  country_id?: number;
  state_id?: number;
  city_id?: number;
  sub_classification_supplier_id?: number;
  supplier_classification_id?: number;
  exclude_zone?: number;
  keys?: string[];
}

export interface SupplierCompleteDataRegistrationParams {
  keys?: string[];
  supplier_classification_id?: number;
  sub_classification_supplier_id?: number;
}

export interface SupplierCompleteDataResponse {
  success: boolean;
  data: {
    supplier_id: number;
    data: {
      supplier_info: {
        id: number;
        code: string;
        business_name: string;
        email: string;
        phone: string;
        country: { id: number; name: string };
        state: { id: number; name: string };
        city: { id: number; name: string };
        zone?: { id: number; name: string };
        supplier_sub_classification_id: number;
        supplier_sub_classification: {
          id: number;
          name: string;
          /** subtypeCode estable entre entornos (nuevo campo del backend) */
          subtypeCode?: string;
          supplier_classification: {
            id: number;
            name: string;
            /** typeCode estable entre entornos (nuevo campo del backend) */
            typeCode?: string;
          };
          supplier_classification_id: number;
        };
        commercial_locations?: string;
        commercial_address?: string;
        fiscal_address?: string;
        geolocation?: { lat: number; lng: number } | null;
        zone_id?: number;
        authorized_management?: boolean;
      };

      general_info: {
        id: number;
        code: string;
        business_name: string;
        company_name: string;
        ruc_number: string;
        dni_number: string;
        authorized_management: boolean;
        status: string;
        supplier_chain: any;
        commercial_locations?: string;
        commercial_address?: string;
        fiscal_address?: string;
        geolocation?: { lat: number; lng: number } | null;
        zone_id?: number;
      };

      contacts: {
        id: number;
        country_phone_code: string;
        state_phone_code: string | null;
        phone: string;
        email: string;
        web: string;
        contacts: Array<{
          id: number;
          supplier_id: number;
          type_contact: { id: number; name: string };
          state: { id: number; name: string };
          full_name: string;
          phone: string;
          email: string | null;
        }>;
      };

      information_commercial: {
        id: number;
        type_food_id: number[];
        classification: number | null;
        amenities: number[];
        spaces: any[];
        schedule: any[];
        schedule_type: number;
        schedule_general: any[];
        additional_information: string | null;
      };

      resources: {
        chains?: {
          chains: Array<{ id: number; name: string }>;
        };

        contacts_states?: {
          typeContacts: Array<{ id: number; name: string }>;
          states: Array<{ id: number; name: string }>;
        } | null;

        countries_phone?: {
          countries_phone: Array<{
            phone_code: string;
            icon: string;
            name: string;
            id: number;
          }>;
        };

        state_phone?: any;

        amenities_foods?: {
          amenities: Array<{ id: number; name: string }>;
          typeFoods: Array<{ id: number; name: string }>;
        } | null;

        classifications?: {
          /** Nueva estructura anidada (backend actualizado) */
          supplierClassifications?: ApiClassificationGroup[];
        };

        zones_locations?: any;

        commercial_resources?: {
          typeFoods: Array<{ id: number; name: string }>;
          amenities: Array<{ id: number; name: string }>;
        };
        locations?: any[];
        zone_locations?: any[];
      };
    };
  };
}

export interface SupplierCompleteDataRegistrationResponse {
  success: boolean;
  data: {
    data: {
      resources: {
        chains?: {
          chains: Array<{ id: number; name: string }>;
        };

        contacts_states?: {
          typeContacts: Array<{ id: number; name: string }>;
          states: Array<{ id: number; name: string }>;
        } | null;

        countries_phone?: {
          countries_phone: Array<{
            phone_code: string;
            icon: string;
            name: string;
            id: number;
          }>;
        };

        state_phone?: any;

        amenities_foods?: {
          amenities: Array<{ id: number; name: string }>;
          typeFoods: Array<{ id: number; name: string }>;
        } | null;

        classifications?: {
          /** Nueva estructura anidada (backend actualizado) */
          supplierClassifications?: ApiClassificationGroup[];
        };

        zones_locations?: any;

        commercial_resources?: {
          typeFoods: Array<{ id: number; name: string }>;
          amenities: Array<{ id: number; name: string }>;
        };
        locations?: any[];
        zone_locations?: any[];
      };
    };
  };
}

// ─── Clave canónica compartida para modo edición ─────────────────────────────
// Todos los composables que leen datos de un proveedor existente deben apuntar
// a esta misma clave para reutilizar el cache de Vue Query sin lanzar peticiones
// HTTP adicionales.
export const SUPPLIER_EDIT_KEYS = [
  'supplier_info',
  'general_info',
  'contacts',
  'chains',
  'classifications',
  'zones_locations',
  'countries_phone',
  'state_phone',
  'contacts_states',
  'commercial_resources',
  'information_commercial',
] as const;

export const getSupplierEditQueryKey = (supplierId: number) =>
  ['supplier', 'edit-complete', supplierId] as const;
// ─────────────────────────────────────────────────────────────────────────────

export const supplierQueryKeys = {
  all: ['supplier'] as const,

  completeData: (supplierId: number, params?: SupplierCompleteDataParams) =>
    [...supplierQueryKeys.all, 'complete-data', supplierId, params] as const,

  completeDataRegistration: (params?: SupplierCompleteDataRegistrationParams) =>
    [...supplierQueryKeys.all, 'complete-data-registration', params] as const,
};

export const useSupplierCompleteDataQuery = (
  supplierId: Ref<number | null | undefined> | number | null | undefined,
  params?: Ref<SupplierCompleteDataParams | undefined> | SupplierCompleteDataParams,
  options?: Omit<UseQueryOptions<SupplierCompleteDataResponse>, 'queryKey' | 'queryFn'>
) => {
  const supplierIdValue = computed(() => unref(supplierId));
  const paramsValue = computed(() => unref(params));

  return useQuery({
    queryKey: computed(() => {
      const key = supplierIdValue.value
        ? supplierQueryKeys.completeData(supplierIdValue.value, paramsValue.value)
        : ['supplier', 'complete-data', 'empty'];

      console.log('key', key);
      return key;
    }),
    queryFn: async () => {
      if (!supplierIdValue.value) {
        throw new Error('Supplier ID is required');
      }
      const response = await useSupplierService.showSupplierCompleteData(
        supplierIdValue.value,
        paramsValue.value
      );

      return response;
    },
    enabled: computed(() => {
      const isEnabled = !!supplierIdValue.value;
      return isEnabled;
    }),
    staleTime: 5 * 60 * 1000, // 5 minutos - cachear datos
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false, // Cambiar a false para evitar llamadas múltiples
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};

export const useSupplierCompleteDataRegistrationQuery = (
  params?:
    | Ref<SupplierCompleteDataRegistrationParams | undefined>
    | SupplierCompleteDataRegistrationParams,
  options?: Omit<UseQueryOptions<SupplierCompleteDataRegistrationResponse>, 'queryKey' | 'queryFn'>
) => {
  const paramsValue = computed(() => unref(params));

  return useQuery({
    queryKey: computed(() => supplierQueryKeys.completeDataRegistration(paramsValue.value)),
    queryFn: async () => {
      const response = await useSupplierService.showSupplierCompleteDataForRegistration(
        paramsValue.value
      );
      return response;
    },
    // staleTime: 5 * 60 * 1000, // 5 minutos - cachear datos de registro
    gcTime: 10 * 60 * 1000,
    retry: 0,
    retryDelay: 0,
    refetchOnWindowFocus: false,
    refetchOnMount: false, // Cambiar a false para evitar llamadas múltiples
    refetchOnReconnect: true,
    networkMode: 'online',
    structuralSharing: false,
    ...options,
  });
};

export const useSupplierCompleteQueries = () => {
  return {
    queryKeys: supplierQueryKeys,

    useCompleteData: useSupplierCompleteDataQuery,
    useCompleteDataRegistration: useSupplierCompleteDataRegistrationQuery,
  };
};

// ─── Query dedicado para el catálogo de clasificaciones ──────────────────────

/**
 * Obtiene el catálogo de clasificaciones desde el MS dedicado.
 * Estable entre entornos (dev/qa/prod) — identificadas por typeCode.
 * Cache de 30 min: las clasificaciones raramente cambian.
 */
export const useSupplierClassificationCatalogQuery = () => {
  return useQuery({
    queryKey: ['supplier-classification-catalog'] as const,
    queryFn: async () => {
      const apiResponse = await useSupplierResourceService.fetchSupplierClassificationCatalog();
      return adaptClassificationsCatalog(apiResponse);
    },
    staleTime: 0,
    gcTime: 60 * 60 * 1000,
    retry: 2,
    refetchOnWindowFocus: false,
    refetchOnMount: false,
    // Remover structuralSharing: false para que Vue Query maneje correctamente los arrays
  });
};

export const extractClassifications = (
  data: SupplierCompleteDataResponse | undefined
): NormalizedClassifications => {
  const apiGroups = data?.data?.data?.resources?.classifications?.supplierClassifications;
  if (apiGroups && apiGroups.length > 0) {
    // This function is no longer needed - classifications come from dedicated endpoint
    console.warn('extractClassifications: Using deprecated nested structure');
  }
  return { classifications: [], subClassifications: [] };
};

export const extractGeneralInfo = (data: SupplierCompleteDataResponse | undefined) => {
  return {
    generalInfo: data?.data?.data?.general_info,
    chains: data?.data?.data?.resources?.chains?.chains || [],
  };
};

export const extractContactInfo = (data: SupplierCompleteDataResponse | undefined) => {
  return {
    contacts: data?.data?.data?.contacts,
    typeContacts: data?.data?.data?.resources?.contacts_states?.typeContacts || [],
    states: data?.data?.data?.resources?.contacts_states?.states || [],
    phoneCountries: data?.data?.data?.resources?.countries_phone?.countries_phone || [],
  };
};

export const extractCommercialInfo = (data: SupplierCompleteDataResponse | undefined) => {
  return {
    commercialInfo: data?.data?.data?.information_commercial,
    typeFoods: data?.data?.data?.resources?.commercial_resources?.typeFoods || [],
    amenities: data?.data?.data?.resources?.commercial_resources?.amenities || [],
  };
};

export const extractLocations = (data: SupplierCompleteDataResponse | undefined) => {
  return {
    locations: data?.data?.data?.resources?.locations || [],
    zoneLocations: data?.data?.data?.resources?.zone_locations || [],
  };
};

// Helper functions for registration mode
export const extractClassificationsForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
): NormalizedClassifications => {
  const apiGroups = data?.data?.data?.resources?.classifications?.supplierClassifications;
  if (apiGroups && apiGroups.length > 0) {
    // This function is no longer needed - classifications come from dedicated endpoint
    console.warn('extractClassificationsForRegistration: Using deprecated nested structure');
  }
  return { classifications: [], subClassifications: [] };
};

export const extractChainsForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
) => {
  return {
    chains: data?.data?.data?.resources?.chains?.chains || [],
  };
};

export const extractPhoneCountriesForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
) => {
  return {
    phoneCountries: data?.data?.data?.resources?.countries_phone?.countries_phone || [],
  };
};

export const extractContactStatesForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
) => {
  return {
    typeContacts: data?.data?.data?.resources?.contacts_states?.typeContacts || [],
    states: data?.data?.data?.resources?.contacts_states?.states || [],
  };
};

export const extractCommercialResourcesForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
) => {
  return {
    typeFoods: data?.data?.data?.resources?.commercial_resources?.typeFoods || [],
    amenities: data?.data?.data?.resources?.commercial_resources?.amenities || [],
  };
};

export const extractLocationsForRegistration = (
  data: SupplierCompleteDataRegistrationResponse | undefined
) => {
  // Locations puede estar en zones_locations.locations o directamente en locations
  const locationsSource =
    data?.data?.data?.resources?.zones_locations?.locations ||
    data?.data?.data?.resources?.locations ||
    [];

  // Zones está en zones_locations.zones
  const zonesSource = data?.data?.data?.resources?.zones_locations?.zones || [];

  return {
    locations: locationsSource,
    zoneLocations: zonesSource,
  };
};
