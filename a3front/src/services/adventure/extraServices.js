import request from '../../utils/requestAdventure';

export function fetchExtraServices(params) {
  return request({
    method: 'GET',
    url: `/extra-services`,
    params,
  });
}

export function fetchExtraService(id) {
  return request({
    method: 'GET',
    url: `/extra-services/${id}`,
  });
}

export function saveExtraService(params) {
  return request({
    method: 'POST',
    url: `/extra-services`,
    data: params,
  });
}

export function updateExtraService(id, params) {
  return request({
    method: 'PATCH',
    url: `/extra-services/${id}`,
    data: params,
  });
}

export function saveTemplateExtraService(template_id, service_id, params) {
  return request({
    method: 'POST',
    url: `/extra-services/${service_id}/add-to-template/${template_id}`,
    data: params,
  });
}

export function saveDepartureExtraService(departure_id, service_id, params) {
  return request({
    method: 'POST',
    url: `/extra-services/${service_id}/add-to-departure/${departure_id}`,
    data: params,
  });
}

export function deleteExtraService(id) {
  return request({
    method: 'DELETE',
    url: `/extra-services/${id}`,
  });
}
