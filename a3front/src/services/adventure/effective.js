import request from '../../utils/requestAdventure';

export function fetchEffective(params) {
  return request({
    method: 'GET',
    url: `/cash-requirements`,
    params,
  });
}

export function updateEffectiveStatus(effective_id, item_id, report_id, type) {
  return request({
    method: 'PATCH',
    url: `cash-requirements/${effective_id}/items/${item_id}/expense-reports/${report_id}/${type}`,
  });
}

export function updateEffectiveStatusItem(effective_id, data) {
  return request({
    method: 'PATCH',
    url: `cash-requirements/${effective_id}/status`,
    data,
  });
}

export function save(effective_id, item_id, data) {
  return request({
    method: 'PUT',
    url: `cash-requirements/${effective_id}/items/${item_id}/expense-reports`,
    data,
  });
}

export function validate(ruc) {
  return request({
    method: 'GET',
    url: `/externals/ifx/users/by-ruc/${ruc}`,
  });
}
