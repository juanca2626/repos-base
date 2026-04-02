import request from '../../utils/requestAurora';

export function fetchCustomers() {
  return request({
    baseURL: window.url_back_a2,
    method: 'GET',
    url: 'api/clients/selectBox/by/executive',
    params: { limit: 40 },
  });
}

export function searchCustomers(query) {
  return request({
    baseURL: window.url_back_a2,
    method: 'GET',
    url: 'api/clients/selectBox/by/executive',
    params: { queryCustom: query, limit: 40 },
  });
}
