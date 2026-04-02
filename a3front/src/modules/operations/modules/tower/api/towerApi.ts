import { fetchData, postData } from '@/api/core/apiClientHelper';
import { API_BASES } from '@/api/constants';

const baseUrl = `${API_BASES.BACKEND_A3}ope/app`;

export const fetchServices = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/control-tower/search`, params, baseUrl);
};

export const fetchIndicators = async (params: any = {}): Promise<any> => {
  return fetchData<any>(`/control-tower/indicators`, params, baseUrl);
};

export const fetchMonitorings = async (operationalServiceId: any = {}): Promise<any> => {
  return fetchData<any>(`/operational-services/monitorings/${operationalServiceId}`, {}, baseUrl);
};

export const addMonitoring = async (payload: any): Promise<any> => {
  return postData<any>(`/operational-services/add-monitoring`, payload, baseUrl);
};
