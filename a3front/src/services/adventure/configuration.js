import request from '../../utils/requestAdventure';

export function fetchConfiguration() {
  return request({
    method: 'GET',
    url: `/configurations`,
  });
}

export function updateConfiguration(id, params) {
  return request({
    method: 'PATCH',
    url: `/configurations/${id}`,
    data: params,
  });
}
