import { API_BASES } from '@/api/constants';
import { fetchData, postData, patchData } from '@/api/core/apiClientHelper';
import { paramsToQueryString } from '@/utils/utils';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchTemplates = async (params: any = {}): Promise<any> => {
  const query = paramsToQueryString(params);
  return fetchData<any>(`/templates?${query}`, {}, baseUrl);
};

export const createTemplate = async (payload: any): Promise<any> => {
  return postData<any>('/templates', payload, baseUrl);
};

export const updateTemplate = async (id: string, payload: any): Promise<any> => {
  return patchData<any>(`/templates/${id}`, payload, baseUrl);
};

export const softDeleteTemplate = async (id: string): Promise<any> => {
  return patchData<any>(`/templates/${id}/delete`, {}, baseUrl);
};

export const cloneTemplate = async (id: string, payload: any): Promise<any> => {
  return postData<any>(`/templates/${id}/clone`, payload, baseUrl);
};
