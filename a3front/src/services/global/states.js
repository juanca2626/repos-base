import request from '../../utils/requestAurora';

export function fetchStates() {
  return request({
    baseURL: window.API_NEGOTIATIONS,
    method: 'GET',
    url: 'neg/support-ms/support/resources?keys[]=states&country_id=89',
    params: {},
  });
}
