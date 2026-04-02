import request from '../../utils/requestAdventure';

export function fetchProgramming(params) {
  return request({
    method: 'GET',
    url: `/programmable`,
    params,
  });
}

export function deactivateProgramming(id) {
  return request({
    method: 'PATCH',
    url: `/programmable/${id}/status`,
  });
}

export function resetProgramming(id) {
  return request({
    method: 'PATCH',
    url: `/programmable/${id}/reset`,
  });
}

export function sendOrder(id) {
  return request({
    method: 'PATCH',
    url: `/programmable/${id}/service-order`,
    data: {
      hasServiceOrder: true,
    },
  });
}

export function saveProgramming(params) {
  return request({
    method: 'POST',
    url: `/programmable`,
    data: params,
  });
}

export function updateProgramming(id, params) {
  return request({
    method: 'PATCH',
    url: `/programmable/${id}`,
    data: params,
  });
}
