import request from '../../utils/requestAdventure';

export function fetchTemplates(params) {
  return request({
    method: 'GET',
    url: `/templates`,
    params,
  });
}

export function fetchTemplate(id) {
  return request({
    method: 'GET',
    url: `/templates/${id}`,
  });
}

export function fetchTemplateServices(type, id) {
  return request({
    method: 'GET',
    url: `/template-services/by-template/${id}`,
  });
}

export function fetchTemplateServicesGrouped(id) {
  return request({
    method: 'GET',
    url: `/template-services/by-template/${id}/grouped`,
  });
}

export function fetchTemplateCashService(id, max) {
  return request({
    method: 'GET',
    url: `/template-services/by-template/${id}/pricing-matrix?minPax=1&maxPax=${max}`,
  });
}

export function fetchTemplateBreakpoints(id) {
  return request({
    method: 'GET',
    url: `/template-services/by-template/${id}/pricing-matrix?minPax=1&maxPax=16`,
  });
}

export function saveTemplate(params) {
  return request({
    method: 'POST',
    url: `/templates`,
    data: params,
  });
}

export function updateTemplate(id, params) {
  return request({
    method: 'PATCH',
    url: `/templates/${id}`,
    data: params,
  });
}

export function deleteTemplate(id) {
  return request({
    method: 'DELETE',
    url: `/templates/${id}`,
  });
}

export function cloneTemplate(id, params) {
  return request({
    method: 'POST',
    url: `/templates/${id}/clone`,
    data: params,
  });
}

export function saveTemplateService(params) {
  return request({
    method: 'POST',
    url: `/template-services`,
    data: params,
  });
}

export function updateTemplateService(id, params) {
  return request({
    method: 'PATCH',
    url: `/template-services/${id}`,
    data: params,
  });
}

export function deleteTemplateService(id) {
  return request({
    method: 'DELETE',
    url: `/template-services/${id}`,
  });
}

export function updateServiceProviders(id, params) {
  return request({
    method: 'PATCH',
    url: `template-services/${id}/provider-scaling`,
    data: params,
  });
}
