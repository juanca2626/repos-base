import request from '../../utils/requestAurora';

export function fetchCurrencies() {
  return request({
    baseURL: window.API_NEGOTIATIONS,
    method: 'GET',
    url: '/support/resources?keys[]=currencies',
    params: {},
  });
}
