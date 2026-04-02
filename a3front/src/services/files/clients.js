import request from '../../utils/request';
import requestAurora from '../../utils/requestAurora';

export function fetchClients(search = '') {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/clients',
    params: { search },
  });
}

export function filterClients(search = '') {
  return requestAurora({
    method: 'GET',
    url: 'api/clients/selectBox/by/executive',
    params: {
      queryCustom: search,
      limit: 10,
    },
  });
}
