import request from '../../utils/request';

export function fetchRevisionStages(langIso = 'es') {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/revision-stages',
    params: { lang_iso: langIso },
  });
}
