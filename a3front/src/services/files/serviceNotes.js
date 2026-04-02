import request from '../../utils/request';

export function fetchServiceNotes({ id, file_id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/list-note-itinerary/${id}`,
  });
}

export function create({ id, file_id, data = { name: '', description: '', date: '' } }) {
  return request({
    baseURL: window.API,
    method: 'POST',
    url: `files/${file_id}/create-note-itinerary/${id}`,
    data,
  });
}

export function update({ note_id, itinerary_id, file_id, data }) {
  return request({
    baseURL: window.API,
    method: 'PUT',
    url: `files/${file_id}/update-note-itinerary/${itinerary_id}/${note_id}`,
    data,
  });
}

export function remove({ note_id, file_id, data }) {
  return request({
    baseURL: window.API,
    method: 'DELETE',
    url: `files/${file_id}/delete-note-itinerary/${note_id}`,
    data: data,
  });
}

export function getClassification() {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/list-note-classification',
  });
}

export function fetchServiceNoteGeneral({ file_id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/notes/general`,
  });
}

export function createNoteGeneral({
  file_id,
  data = { date_event: '', type_event: '', description_event: '', image_logo: '' },
}) {
  return request({
    baseURL: window.API,
    method: 'POST',
    url: `files/${file_id}/notes/general`,
    data,
  });
}

export function updateNoteGeneral({ note_id, file_id, data }) {
  return request({
    baseURL: window.API,
    method: 'PUT',
    url: `files/${file_id}/notes/general/${note_id}`,
    data,
  });
}

export function listNote({ file_id }) {
  return request({
    baseURL: window.API,
    method: 'POST',
    url: `files/${file_id}/note`,
  });
}

export function createNote({
  file_id,
  data = {
    type_note: '',
    record_type: '',
    assignment_mode: '',
    dates: '',
    description: '',
    classification_code: '',
    classification_name: '',
    created_by: '',
    created_by_name: '',
    services_ids: '',
  },
}) {
  return request({
    baseURL: window.API,
    method: 'POST',
    url: `files/${file_id}/note`,
    data,
  });
}

export function updateNote({
  file_id,
  id,
  data = {
    type_note: '',
    record_type: '',
    assignment_mode: '',
    dates: '',
    description: '',
    classification_code: '',
    classification_name: '',
    created_by: '',
    created_by_name: '',
    services_ids: '',
  },
}) {
  return request({
    baseURL: window.API,
    method: 'PUT',
    url: `files/${file_id}/note/${id}`,
    data,
  });
}

export function deleteNote({ file_id, id, data = {} }) {
  return request({
    baseURL: window.API,
    method: 'DELETE',
    url: `files/${file_id}/note/${id}`,
    params: data,
  });
}

export function findExternalHousing({ file_id, id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/note/external/housing/${id}`,
  });
}

export function listExternalHousing({ file_id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/note/external/housing`,
  });
}

export function createExternalHousing({ file_id, data }) {
  return request({
    baseURL: window.API,
    method: 'POST',
    url: `files/${file_id}/note/external/housing`,
    params: data,
  });
}

export function updateExternalHousing({ file_id, id, data }) {
  return request({
    baseURL: window.API,
    method: 'PUT',
    url: `files/${file_id}/note/external/housing/${id}`,
    params: data,
  });
}

export function deleteExternalHousing({ file_id, id }) {
  return request({
    baseURL: window.API,
    method: 'DELETE',
    url: `files/${file_id}/note/external/housing/${id}`,
  });
}

export function fetchAllFileNotes({ file_id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/note/all`,
  });
}

export function fetchAllRequirementFileNotes({ file_id }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${file_id}/note/all/requirement`,
  });
}
