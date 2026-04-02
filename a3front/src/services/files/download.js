import request from '../../utils/request';

export function download({
  currentPage,
  perPage,
  filter = '',
  executiveCode = '',
  clientId = '',
  dateRange = '',
  filterNextDays = '',
  revisionStages = '',
}) {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'files/download',
    responseType: 'blob',
    params: {
      page: currentPage,
      per_page: perPage,
      filter,
      filter_by: '',
      filter_by_type: '',
      executive_code: executiveCode,
      client_id: clientId,
      date_range: dateRange,
      filter_next_days: filterNextDays,
      revision_stages: revisionStages,
    },
  });
}

export function downloadFilePassengers(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/passenger-export`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function downloadFileFlights(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/export-itinerary-flights`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function downloadFileInvoice(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/statement/download`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function downloadFileSkeleton(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/skeleton-pdf`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function downloadFileItinerary(fileId, imageCover, lang = 'es', type = 'pdf') {
  return request({
    method: 'GET',
    url: `files/${fileId}/itinerary-download-${type}`,
    responseType: 'blob',
    params: {
      lang,
      portada: imageCover ? imageCover : '',
    },
  });
}

export function downloadFileServiceSchedule(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/service-export-schedule`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function downloadFileListHotels(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/hotel-list/${fileId}/export`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function fetchStatementDetailsByFileId(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/statement/details`,
  });
}

export function fetchListHotelsByFileId(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/hotel-list`,
  });
}

export function downloadRoomingListExcel(fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${fileId}/romming-list-excel`,
    responseType: 'blob',
    params: {
      lang,
    },
  });
}

export function fetchSkeletonByFileId(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/skeleton`,
  });
}

export function downloadStatementCreditNote(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/credit-note/download`,
    responseType: 'arraybuffer',
    headers: {
      Accept: 'application/pdf',
    },
  });
}

export function downloadStatementDebitNote(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/debit-note/download`,
    responseType: 'blob',
  });
}

export function downloadFileBalance({
  currentPage,
  perPage,
  filter,
  filterBy,
  filterByType,
  executiveCode,
  clientId,
  dateRange,
}) {
  return request({
    method: 'GET',
    url: `files/balance/download`,
    responseType: 'blob',
    params: {
      page: currentPage,
      per_page: perPage,
      filter,
      filter_by: filterBy,
      filter_by_type: filterByType,
      executive_code: executiveCode,
      client_id: clientId,
      date_range: dateRange,
    },
  });
}
