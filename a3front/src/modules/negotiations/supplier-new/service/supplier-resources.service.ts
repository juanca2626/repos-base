import { supplierApi, directSupportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { Chain } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

const baseResourceUrl = 'supplier/resources';

async function fetchSupplierSubClassification(): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: { 'keys[]': ['supplierSubClassification', 'supplierClassification'] },
  });
  return response.data;
}

async function fetchLocations(country_id: number, exclude_zone: number = 0): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: { 'keys[]': 'locations', country_id: country_id, exclude_zone },
  });
  return response.data;
}

async function fetchCountryStateLocations(country_id: number): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: { 'keys[]': 'countryStateLocations', country_id: country_id },
  });
  return response.data;
}

async function fetchZoneLocations(
  country_id: number,
  state_id: number | null = null,
  city_id: number | null = null,
  exclude_zone: number = 0
): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: {
      'keys[]': 'zonesLocations',
      country_id: country_id,
      state_id: state_id,
      city_id: city_id,
      exclude_zone,
    },
  });
  return response.data;
}

async function fetchSunatInformation(): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: {
      'keys[]': ['taxRates', 'ivaOptions'],
    },
  });
  return response.data;
}

async function fetchModuleSunatInformation(): Promise<any> {
  const response = await supplierApi.get('supplier/tributary-information/resources', {
    params: {
      'keys[]': ['typeTaxDocument', 'cities'],
    },
  });
  return response.data;
}

async function fetchModuleContactResource(): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: {
      'keys[]': ['typeContact', 'departments'],
    },
  });
  return response.data;
}

async function fetchOperationLocations(subClassificationSupplierId: any): Promise<any> {
  const response = await supplierApi.get(
    `supplier/operation-locations/by-sub-classification/${subClassificationSupplierId}`
  );
  return response.data;
}

async function fetchPoliciesResource(keys: string[]): Promise<any> {
  const response = await supplierApi.get('supplier-policy-resources', {
    params: {
      keys,
    },
  });
  return response.data;
}

async function fetchHolidayCalendars(
  date_from: string | null,
  date_to: string | null
): Promise<any> {
  const response = await supplierApi.get('supplier-policy-resources', {
    params: {
      'keys[]': ['calendar'],
      date_from: date_from,
      date_to: date_to,
    },
  });
  return response.data;
}

async function fetchInformationCommercialResource(subClassificationSupplierId: any): Promise<any> {
  const params: Record<string, any> = {
    'keys[]': ['amenities', 'typeFoods'],
  };

  if (subClassificationSupplierId) {
    params.subClassificationSupplierId = subClassificationSupplierId;
  }

  const response = await supplierApi.get('supplier/resources', { params });
  return response.data;
}

async function fetchModuleBankInformationLocationsResource(
  country_id: number,
  keys: any = ['countries', 'states']
): Promise<any> {
  const response = await supplierApi.get('supplier/resources', {
    params: {
      'keys[]': keys,
      country_id: country_id,
    },
  });
  return response.data;
}

async function fetchModuleBankInformationResource(): Promise<any> {
  const response = await supplierApi.get('supplier/bank-information/resources', {
    params: {
      'keys[]': ['typeBankAccount', 'bank', 'typeDocument'],
    },
  });
  return response.data;
}

async function fetchChains(supplierClassificationId: number): Promise<Chain[]> {
  const response = await supplierApi.get(baseResourceUrl, {
    params: {
      keys: ['chains'],
      supplier_classification_id: supplierClassificationId,
    },
  });
  return response.data.data.chains;
}

async function fetchChainsByCode(supplierClassificationCode: string): Promise<Chain[]> {
  const response = await supplierApi.get('chains', {
    params: {
      classification_code: supplierClassificationCode,
    },
  });
  return response.data.data || [];
}

async function fetchSupplierListOptions(): Promise<any> {
  const response = await directSupportApi.get('suppliers/with-policies');
  return response.data;
}

async function fetchSupplierClassificationCatalog(): Promise<any> {
  const response = await supplierApi.get('supplier-classifications/type-codes');
  return response.data;
}

export const useSupplierResourceService = {
  fetchSupplierSubClassification,
  fetchLocations,
  fetchCountryStateLocations,
  fetchZoneLocations,
  fetchSunatInformation,
  fetchModuleSunatInformation,
  fetchModuleContactResource,
  fetchOperationLocations,
  fetchPoliciesResource,
  fetchModuleBankInformationLocationsResource,
  fetchModuleBankInformationResource,
  fetchChains,
  fetchChainsByCode,
  fetchInformationCommercialResource,
  fetchHolidayCalendars,
  fetchSupplierListOptions,
  fetchSupplierClassificationCatalog,
};
