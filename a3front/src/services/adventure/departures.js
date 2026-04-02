import request from '../../utils/requestAdventure';

export function fetchDepartures(params) {
  return request({
    method: 'GET',
    url: `/departures`,
    params,
  });
}

export function exportDepartures(params) {
  return request({
    method: 'GET',
    url: `/departures/export/excel`,
    params,
    responseType: 'blob',
  });
}

export function fetchDepartureServices(id) {
  return request({
    method: 'GET',
    url: `/template-services/by-departure/${id}`,
    params: {
      onlyExtras: true,
      currency: 'PEN',
    },
  });
}

export function fetchDepartureTemplateServices(id) {
  return request({
    method: 'GET',
    url: `/template-services/quote/${id}`,
  });
}

export function fetchCalendarDepartures(params) {
  return request({
    method: 'GET',
    url: `/departures/by-month`,
    params,
  });
}

export function fetchDeparture(id) {
  return request({
    method: 'GET',
    url: `/departures/${id}`,
  });
}

export function fetchPaxsByFile(file) {
  return request({
    method: 'GET',
    url: `/externals/ifx/file/${file}/paxs`,
  });
}

export function fetchGuides(params, types) {
  return request({
    method: 'GET',
    url: `/externals/ifx/providers`,
    params: {
      ...params,
      types: types || '',
    },
  });
}

export function fetchCashServices(departure_id, params) {
  return request({
    method: 'GET',
    url: `/cash-requirements/services/${departure_id}`,
    params,
  });
}

export function generateCashServices(departure_id, type) {
  const url = type ? `/cash-requirements/generate-${type}` : `/cash-requirements/generate`;
  return request({
    method: 'POST',
    url,
    data: {
      departureId: departure_id,
    },
  });
}

export function updateCash(id, status) {
  return request({
    method: 'PATCH',
    url: `/cash-requirements/${id}/status`,
    data: {
      status,
    },
  });
}

export function savePaxsToDeparture(departure_id, params) {
  return request({
    method: 'POST',
    url: `/departures/${departure_id}/paxs`,
    data: params,
  });
}

export function deletePaxToDeparture(departure_id, params) {
  return request({
    method: 'DELETE',
    url: `/departures/${departure_id}/paxs`,
    data: params,
  });
}

export function saveDeparture(params) {
  return request({
    method: 'POST',
    url: `/departures`,
    data: params,
  });
}

export function updateDeparture(id, params) {
  return request({
    method: 'PATCH',
    url: `/departures/${id}`,
    data: params,
  });
}

export function deleteDeparture(id) {
  return request({
    method: 'DELETE',
    url: `/departures/${id}`,
  });
}

export function deactivateDeparture(id) {
  return request({
    method: 'PATCH',
    url: `/departures/${id}/deactivate`,
  });
}

export function closeFile(id, payload) {
  return request({
    method: 'PATCH',
    url: `/departures/${id}/close`,
    data: payload,
  });
}

export function deleteCloseProcess(id, payload) {
  return request({
    method: 'PATCH',
    url: `/departures/${id}/reopen`,
    data: payload,
  });
}

export function hideService(id, payload) {
  return request({
    method: 'PATCH',
    url: `/template-services/${id}`,
    data: payload,
  });
}

export function closeService(id, payload) {
  return request({
    method: 'PATCH',
    url: `/template-services/${id}`,
    data: payload,
  });
}

export function updateRealCost(id, payload) {
  return request({
    method: 'PATCH',
    url: `/template-services/${id}`,
    data: payload,
  });
}

export function updateTemplateServiceProviders(id, payload) {
  return request({
    method: 'PATCH',
    url: `/template-services/${id}`,
    data: payload,
  });
}

export function resendNotification(id, status) {
  return request({
    method: 'POST',
    url: `/cash-requirements/${id}/resend-notification`,
    data: {
      status,
    },
  });
}
