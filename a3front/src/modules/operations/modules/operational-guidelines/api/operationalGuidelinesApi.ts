import { fetchData, postData } from '@/api/core/apiClientHelper';
import type { ApiResponse } from '@/api/interfaces/response.interface';
import { API_BASES } from '@/api/constants';

import type {
  Guideline,
  Headquarter,
  Owner,
  Provider,
} from '@operations/modules/operational-guidelines/interfaces';

const baseUrl = API_BASES.OPERATIONAL_GUIDELINES_SERVICE;
const baseUrl_EventsPublisherService = API_BASES.EVENTS_PUBLISHER_SERVICE;

export const syncData = async (data: any) => {
  return postData<any>('/event', data, baseUrl_EventsPublisherService);
};

// Función para obtener las locaciones
export const fetchHeadquarters = async (): Promise<ApiResponse<Headquarter> | null> => {
  return fetchData<Headquarter>('/headquarters', {}, baseUrl);
};

// Función para obtener todas las pautas operativas
export const fetchGuidelines = async (): Promise<ApiResponse<Guideline> | null> => {
  return fetchData<Guideline>('/guidelines', {}, baseUrl);
};

export const fetchOwners = async (
  type: string,
  query: string
): Promise<ApiResponse<Owner> | null> => {
  return fetchData<Owner>(`/owners?type=${type}&search=${query}`, {}, baseUrl);
};

// Función para obtener un 'owner': Cliente / Mercado específico
export const fetchOwner = async (code: string): Promise<ApiResponse<Owner> | null> => {
  return fetchData<Owner>(`/owners/${code}`, {}, baseUrl);
};

// Función para obtener los proveedores
export const fetchProviders = async (
  searchText: string,
  type: string
): Promise<ApiResponse<Provider> | null> => {
  return fetchData<Provider>(
    `/providers?page=1&limit=10&search=${searchText}&type=${type}`,
    {},
    baseUrl
  );
};

// Función específica para obtener pautas operativas por su ID (cliente/mercado/serie) con uso temporal de `any`
export const fetchOperationalGuidelines = async (id: string): Promise<ApiResponse<any> | null> => {
  return fetchData<any>(`/operational-guidelines/${id}`, {}, baseUrl);
};

export const addOperationalGuideline = async (operationalGuidelineData: any) => {
  try {
    return postData<any>('/operational-guidelines', operationalGuidelineData, baseUrl);
  } catch (error) {
    throw error;
  }
};
