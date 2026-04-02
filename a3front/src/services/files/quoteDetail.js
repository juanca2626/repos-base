import request from '../../utils/request';

export function fetchQuoteDetail({ itineraryId = '' }) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: `files/${itineraryId}/itinerary`,
    params: {},
  });
}
