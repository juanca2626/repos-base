import request from '../../utils/request';
export function fetchStatuses(langIso = 'es') {
  return request({
    method: 'GET',
    url: 'files/status',
    params: { lang_iso: langIso },
  });
}
