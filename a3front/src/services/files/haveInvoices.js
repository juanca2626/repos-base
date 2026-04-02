import request from '../../utils/request';

export function fetchHaveInvoices(langIso = 'es') {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/have-invoice',
    params: { lang_iso: langIso },
  });
}
