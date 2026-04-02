import { API_BASES } from '@/api/constants';
import { fetchData } from '@/api/core/apiClientHelper';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchProducts = async (params: any = {}): Promise<any> => {
  return fetchData<any>('/products', params, baseUrl);
};
