import request from '../../utils/requestFilesOneDB.js';

export function fetchRoutes(filters = {}) {
  return request({
    method: 'POST',
    url: '/api/routes',
    data: filters,
  });
}
export function saveRoute(route) {
  return request({
    method: 'POST',
    url: '/api/routes/save',
    data: route,
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
