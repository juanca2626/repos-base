import { API_BASES } from '@/api/constants';
import { fetchData } from '@/api/core/apiClientHelper';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchOrderStatus = async (): Promise<any> => {
  return fetchData<any>(`/order-status`, {}, baseUrl);
};
