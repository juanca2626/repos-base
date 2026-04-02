import { API_BASES } from '@/api/constants';
import { fetchData } from '@/api/core/apiClientHelper';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchTypes = async (params: any = {}): Promise<any> => {
  return fetchData<any>('/types', params, baseUrl);
};
