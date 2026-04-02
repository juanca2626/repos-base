import request from '../../utils/requestFilesOneDB.js';

export function fetchClaims(filters = {}) {
  return request({
    method: 'POST',
    url: '/api/claims',
    data: filters,
  });
}
export function saveClaim(claim) {
  return request({
    method: 'POST',
    url: '/api/claims/save',
    data: claim,
  });
}
// Nueva función para buscar archivos por número de file
import requestAurora from '../../utils/requestAurora'; // Importamos otro módulo de request

export function searchFile(nroFile) {
  return requestAurora({
    baseURL: 'https://extranet.litoapps.com/backend/controllers/',
    method: 'POST',
    url: 'CustomerService.php',
    data: { nroFile },
  });
}
