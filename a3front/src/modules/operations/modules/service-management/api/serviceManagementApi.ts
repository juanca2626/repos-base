import { fetchData, patchData, postData, removeData } from '@/api/core/apiClientHelper';
import { API_BASES } from '@/api/constants';

const baseUrl = `${API_BASES.BACKEND_A3}ope/app`;

export const fetchServices = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/service-management/search`, params, baseUrl);
};

export const fetchIndicators = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/service-management/indicators`, params, baseUrl);
};

export const nestServices = async (payload: any): Promise<any> => {
  return postData<any>(`/service-management/nest`, payload, baseUrl);
};

export const unnestServices = async (payload: any): Promise<any> => {
  return postData<any>(`/service-management/unnest`, payload, baseUrl);
};

export const fetchOperationalGuidelines = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`generals/og`, params, baseUrl);
};

export const fetchProviders = async (type: string, params: any = {}): Promise<any> => {
  if (type === 'GUI') {
  } else {
  }
  return fetchData<any>(`generals/providers`, params, baseUrl);
};

export const fetchGuides = async (params: any = {}): Promise<any> => {
  // ?client=5neg&headquarter=lim&guideline=rep&datetime_start=2024-10-01T11:00&datetime_end=2024-12-30T13:00
  return fetchData<any>(`providers/guides`, params, baseUrl);
};

// Obtener proveedores preferentes
export const fetchPreferredProviders = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`providers/preferred-ones`, params, baseUrl);
};

// Obtener proveedores por término de búsqueda (código o nombre)
export const fetchProviderByTerm = async (type: 'gui' | 'trp', params: any = {}): Promise<any> => {
  return fetchData<any>(`providers/search/${type}`, params, baseUrl);
};

/* SERVICIOS ADICIONALES */
// Servicios adicionales de programación
export const fetchAdditionals = async (operational_service_id: string = ''): Promise<any> => {
  return fetchData<any>(`service-management/additionals/${operational_service_id}`, {}, baseUrl);
};

export const fetchNotesByFile = async (file: string): Promise<any> => {
  return fetchData<any>(`external/files/notes/${file}`, {}, baseUrl);
};

export const fetchZones = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`generals/zones?process=file-to-ope`, params, baseUrl);
};

export const fetchTrp = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`generals/trp/`, params, baseUrl);
};

// Realiza una solicitud POST para crear servicios adicionales
export const createAdditionals = async (payload: any): Promise<any> => {
  return postData<any>(`/service-management/additionals`, payload, baseUrl);
};

// ASSIGNMENT
export const assignment = async (payload: any): Promise<any> => {
  return patchData<any>(`service-management/assignment`, payload, baseUrl);
};

export const updateAssignment = async (assignmentId: string, payload: any): Promise<any> => {
  return patchData<any>(`assignment/${assignmentId}`, payload, baseUrl);
};

export const removeAssignment = async (assignmentId: string): Promise<any> => {
  return removeData<any>(`assignment/${assignmentId}`, {}, baseUrl);
};

export const createServiceOrder = async (payload: any): Promise<any> => {
  return postData<any>(`service-order/`, payload, baseUrl);
};

export const createReturnApi = async (payload: any): Promise<any> => {
  return postData<any>(`operational-services/create-return`, payload, baseUrl);
};
