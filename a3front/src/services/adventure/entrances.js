import request from '../../utils/requestAdventure';

export function fetchEntrances(params) {
  return request({
    method: 'GET',
    url: `/tickets`,
    params,
  });
}

export function fetchService(id, params) {
  return request({
    method: 'GET',
    url: `/entry-requirements/service/${id}`,
    params,
  });
}

export function updateStatus(id, params) {
  return request({
    method: 'PATCH',
    url: `/entry-requirements/${id}/status`,
    data: params,
  });
}

export function saveEntrance(params) {
  return request({
    method: 'POST',
    url: `/template-services/tickets`,
    data: params,
  });
}

export function reserveEntrance(id, params) {
  return request({
    method: 'POST',
    url: `/tickets/${id}`,
    data: params,
  });
}

export function downloadEntrance(id) {
  return request({
    method: 'GET',
    url: `/tickets/${id}/download`,
    responseType: 'blob',
  });
}

export function sendEntrance(id, params) {
  return request({
    method: 'POST',
    url: `/tickets/${id}/resend-notification`,
    data: params,
  });
}
