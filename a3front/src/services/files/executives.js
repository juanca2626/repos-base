import request from '../../utils/request';
import requestAurora from '../../utils/requestAurora';

export function fetchExecutives(array_codes = '', search = '') {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'catalogs/executives',
    params: {
      array_codes: array_codes,
      search: search,
    },
  });
}

export function fetchBoss(array_codes, search = '') {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/boss',
    params: {
      array_codes: array_codes,
      search: search,
    },
  });
}

export function fetchSelectBox(code) {
  return requestAurora({
    baseURL: window.API,
    method: 'GET',
    url: 'api/executives/selectBox',
    params: {
      code: code,
    },
  });
}
