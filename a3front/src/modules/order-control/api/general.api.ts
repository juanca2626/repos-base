import { API_BASES } from '@/api/constants';
import { fetchData } from '@/api/core/apiClientHelper';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchBootstrapData = async (): Promise<any> => {
  return fetchData<any>('/general/bootstrap', {}, baseUrl);
};

export const fetchLanguages = async (params: any = {}): Promise<any> => {
  return fetchData<any>('/general/languages', params, baseUrl);
};

export const fetchTeamByUser = async (code: string): Promise<any> => {
  return fetchData<any>(`/external/ifx/executives/${code}/team`, {}, baseUrl);
};
