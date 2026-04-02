import { fetchData, patchData, postData } from '@/api/core/apiClientHelper';
import { API_BASES } from '@/api/constants';

const baseUrl = `${API_BASES.BACKEND_A3}ope/app`;

export const fetchServices = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/scheduled-services/search`, params, baseUrl);
};

export const fetchIndicators = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/scheduled-services/indicators`, params, baseUrl);
};

export const fetchDriversByProvider = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/drivers/search`, params, baseUrl);
};

export const fetchVehiclesByProvider = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/vehicles/search`, params, baseUrl);
};

export const fetchProcess = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/processes/report-incident-types`, params, baseUrl);
};

export const fecthHistoryIncidents = async (id: string): Promise<any> => {
  return fetchData<any>(`/reports/operational-service/${id}`, {}, baseUrl);
};

export const updateAssignment = async (assignmentId: string, payload: any): Promise<any> => {
  return patchData<any>(`assignment/${assignmentId}`, payload, baseUrl);
};

export const confirmAssignments = async (payload: any): Promise<any> => {
  return postData<any>(`assignment/confirmations`, payload, baseUrl);
};

export const createReport = async (payload: any): Promise<any> => {
  return postData<any>(`reports`, payload, baseUrl);
};

export const uploadImage = async (payload: any): Promise<any> => {
  return postData<any>(
    '/s3/upload',
    payload,
    'https://xnbdpvozue.execute-api.us-east-1.amazonaws.com/dev',
    false
  );
};
