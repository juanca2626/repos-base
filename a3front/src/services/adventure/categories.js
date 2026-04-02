import request from '../../utils/requestAdventure';

export function fetchCategories(search = '') {
  return request({
    method: 'GET',
    url: `/categories`,
    params: { search },
  });
}

export function saveCategory(params) {
  return request({
    method: 'POST',
    url: `/categories`,
    data: params,
  });
}

export function updateCategory(id, params) {
  return request({
    method: 'PATCH',
    url: `/categories/${id}`,
    data: params,
  });
}
