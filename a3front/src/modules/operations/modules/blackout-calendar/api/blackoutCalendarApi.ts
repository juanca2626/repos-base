import { fetchData, patchData, postData, removeData } from '@/api/core/apiClientHelper';
import type { ApiResponse } from '@/api/interfaces/response.interface';
import { API_BASES } from '@/api/constants';

import type {
  ProviderContractType,
  ProviderProfileType,
  Headquarter,
  BlockingReason,
  Provider,
} from '@operations/modules/blackout-calendar/interfaces';

const baseUrl = API_BASES.BLOCK_CALENDAR_SERVICE;

// Obtener tipos de contrato
export const fetchProviderContractTypes =
  async (): Promise<ApiResponse<ProviderContractType> | null> => {
    return fetchData<ProviderContractType>('/provider-contract-types', {}, baseUrl);
  };

// Obtener tipos de perfiles de proveedor
export const fetchProviderProfileTypes =
  async (): Promise<ApiResponse<ProviderProfileType> | null> => {
    return fetchData<ProviderProfileType>('/provider-profile-types', {}, baseUrl);
  };

// Obtener locaciones
export const fetchHeadquarters = async (): Promise<ApiResponse<Headquarter> | null> => {
  return fetchData<Headquarter>('/headquarters', {}, baseUrl);
};

// Obtener razones de bloqueo
export const fetchBlockingReasons = async (): Promise<ApiResponse<BlockingReason> | null> => {
  return fetchData<BlockingReason>('/blocking-reasons', {}, baseUrl);
};

// Obtener proveedores por búsqueda
export const fetchProviders = async (searchText: string): Promise<ApiResponse<Provider> | null> => {
  return fetchData<Provider>('/providers', { page: 1, limit: 10, search: searchText }, baseUrl);
};

// Obtener bloqueos por mes
export const fetchLocksByMonth = async (params: {
  month: string;
  year: string;
  contractProvider: string;
  profileProvider: string;
  headquarter: string;
  searchTerm: string;
}): Promise<ApiResponse<any> | null> => {
  return fetchData<any>('/locks/month', params, baseUrl);
};

// Crear un nuevo bloqueo
export const createLockApi = async (newLockData: any): Promise<ApiResponse<any> | null> => {
  return postData<any>('/locks', newLockData, baseUrl);
};

// Actualizar un bloqueo existente
export const updateLockApi = async (
  lockId: string,
  updateLockData: any
): Promise<ApiResponse<any> | null> => {
  return patchData<any>(`/locks/${lockId}`, updateLockData, baseUrl);
};

// Eliminar un grupo de bloqueos
export const deleteLocksApi = async (locks: any): Promise<ApiResponse<any> | null> => {
  return removeData<any>('/locks', locks, baseUrl);
};

// Obtener bloqueos por proveedor y fechas
export const fetchLocksByProvidersAndSelectedDaysApi = async (
  payload: any
): Promise<ApiResponse<any> | null> => {
  return postData<any>('/locks/fetch-by-providers-and-days', payload, baseUrl);
};
