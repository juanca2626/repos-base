import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SupplierContactResponse } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

const baseResourceUrl = 'supplier';

async function fetchResources(countryId: number, supplierId?: number): Promise<any> {
  const response = await supplierApi.get(`${baseResourceUrl}/resources`, {
    params: {
      keys: ['typeContacts', 'states'],
      country_id: countryId,
      ...(supplierId && { supplier_id: supplierId }),
    },
  });
  return response.data.data;
}

async function fetchPhoneCountryResources(): Promise<any> {
  const response = await supplierApi.get(`${baseResourceUrl}/resources`, {
    params: {
      keys: ['countries_phone'],
    },
  });
  return response.data.data;
}

async function fetchPhoneStateResources(countryId: number): Promise<any> {
  const response = await supplierApi.get(`${baseResourceUrl}/resources`, {
    params: {
      keys: ['state_phone'],
      country_id: countryId,
    },
  });
  return response.data.data;
}

async function updateSupplierContact(supplierId: number, attributes: any): Promise<any> {
  const response = await supplierApi.patch(`${baseResourceUrl}/contacts/${supplierId}`, attributes);

  return response.data;
}

// Nuevo endpoint específico para estados por país
async function fetchStatesByCountry(countryId: number): Promise<any> {
  const response = await supplierApi.get(`locations/states/by-country`, {
    params: {
      country_id: countryId,
    },
  });
  return response.data;
}

async function showSupplierContact(
  supplierId: number
): Promise<ApiResponse<SupplierContactResponse>> {
  const response = await supplierApi.get(`${baseResourceUrl}/contacts/${supplierId}`);

  return response.data;
}

export const useSupplierContactInformationService = {
  fetchResources,
  fetchPhoneCountryResources,
  fetchPhoneStateResources,
  updateSupplierContact,
  showSupplierContact,
  fetchStatesByCountry,
};
