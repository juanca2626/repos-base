import request from '../../utils/request';

export function fetchItinerary({ fileId, itineraryId }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `direct/files/${fileId}/itineraries/${itineraryId}`,
  });
}
export function fetchQuotation({ itineraryId }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${itineraryId}/itinerary`,
  });
}
export function addFileItinerary({ fileId, data }) {
  return request({
    method: 'POST',
    url: `direct/files/${fileId}/itineraries`,
    data: data,
  });
}
export function updateFileItinerary({ fileId, fileItineraryId, data }) {
  return request({
    method: 'PUT',
    url: `direct/files/${fileId}/itineraries/${fileItineraryId}`,
    data: data,
  });
}

export function addFileItineraryPublic({ fileId, data }) {
  return request({
    method: 'POST',
    url: `direct/files/${fileId}/itineraries`,
    data: data,
  });
}
export function updateFileItineraryPublic({ fileId, fileItineraryId, data }) {
  return request({
    method: 'PUT',
    url: `direct/files/${fileId}/itineraries/${fileItineraryId}`,
    data: data,
  });
}
