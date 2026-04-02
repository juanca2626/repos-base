import { API_BASES } from '@/api/constants';
import { fetchData } from '@/api/core/apiClientHelper';
import { paramsToQueryString } from '@/utils/utils';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchUsers = async (params: any = {}): Promise<any> => {
  const query = paramsToQueryString(params);
  return fetchData<any>(`/users?${query}`, {}, baseUrl);
};

export const fetchUsersExternal = async (): Promise<any> => {
  return fetchData<any>(`/users/external`, {}, baseUrl);
};
