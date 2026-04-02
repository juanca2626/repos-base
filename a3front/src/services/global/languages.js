import request from '../../utils/request';
import requestAurora from '../../utils/requestAurora';

export function fetchLanguages() {
  return request({
    method: 'GET',
    url: 'direct/languages',
    params: {},
  });
}

export function fetchAllLanguages() {
  return requestAurora({
    method: 'GET',
    url: 'api/languages/all',
    params: {},
  });
}
