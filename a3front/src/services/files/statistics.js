import request from '../../utils/request';

export function fetchStatistics() {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/statistics',
    params: {},
  });
}
