import { getAccessToken, getUserCode, getUserId, getUserName } from '../../utils/auth';
import request from '../../utils/request';
import requestAurora from '../../utils/requestAurora';
import requestNode from '../../utils/requestNode';
import requestSQS from '../../utils/requestSQS';

const STAGE = import.meta.env.VITE_APP_ENV;

const DEFAULT_SQS_AMAZON = `sqs-files-sync-update-${STAGE}`;
const ACCOMMODATIONS_SQS_AMAZON = `sqs-files-sync-accommodations-${STAGE}`;
const PASSENGERS_SQS_AMAZON = `sqs-files-sync-passengers-${STAGE}`;
const SCHEDULE_SQS_AMAZON = `sqs-files-sync-schedule-${STAGE}`;
const DATE_SQS_AMAZON = `sqs-files-sync-date-${STAGE}`;
const AMOUNTS_SQS_AMAZON = `sqs-files-sync-amounts-${STAGE}`;
const ADD_SQS_AMAZON = `sqs-files-sync-add-${STAGE}`;
const CANCELLATION_SQS_AMAZON = `sqs-files-sync-cancellation-${STAGE}`;
const CANCEL_SQS_AMAZON = `sqs-files-sync-cancel-file-${STAGE}`;
const ACTIVATE_SQS_AMAZON = `sqs-files-sync-activate-file-${STAGE}`;
const CONFIRMATION_CODE_SQS_AMAZON = `sqs-files-sync-confirmation-code-${STAGE}`;
const STATEMENT_SQS_AMAZON = `sqs-files-sync-statement-${STAGE}`;
const DEFAULT_EMAIL_NOTIFY = 'lsv@limatours.com.pe';
const DEFAULT_SERVICE = 'files';

export function fetchFiles({
  currentPage,
  perPage,
  filter = '',
  filterBy = '',
  filterByType = '',
  executiveCode = '',
  clientId = '',
  dateRange = '',
  filterNextDays = '',
  revisionStages = '',
  opeAssignStages = false,
  complete = 0,
  flag_stella,
}) {
  const params = {
    page: currentPage,
    per_page: perPage,
    filter,
    filter_by: filterBy,
    filter_by_type: filterByType,
    executive_code: executiveCode,
    client_id: clientId,
    date_range: dateRange,
    filter_next_days: filterNextDays,
    revision_stages: revisionStages,
    ope_assign_stages: opeAssignStages,
    ...(complete !== 0 && { complete }),
  };

  if (flag_stella) {
    return request({
      method: 'POST',
      url: 'files/list-files-stela',
      data: params,
    });
  } else {
    return request({
      method: 'GET',
      url: 'files',
      params: params,
    });
  }
}

export function getFileBasic({ fileId }) {
  return request({
    method: 'GET',
    url: `files/basic/${fileId}`,
    params: {},
  });
}

export function getFile({ id, nrofile }) {
  let url = '';

  if (id !== undefined) {
    url = `files/${id}`;
  }

  if (nrofile !== undefined) {
    url = `direct/files/number/${nrofile}`;
  }

  return request({
    method: 'GET',
    url: url,
    params: {},
  });
}

export function getFilePublic({ nrofile, data = { code_hotel: '' } }) {
  return request({
    method: 'GET',
    url: `direct/files/basic/${nrofile}`,
    params: data,
  });
}

export function getLatestItineraries({ fileNumber, itineraryId }) {
  return request({
    method: 'GET',
    url: `files/latest-itineraries`,
    params: {
      file_number: fileNumber,
      file_itinerary_id: itineraryId,
    },
  });
}

export function getItinerary({ itinerary_id }) {
  return request({
    method: 'GET',
    url: `files/file_itinerary/${itinerary_id}`,
    params: {},
  });
}

export function getFileReasons(params) {
  return request({
    method: 'GET',
    url: `reasons-rate`,
    params: params,
  });
}

export function getStatementReasons() {
  return request({
    method: 'GET',
    url: `files/statement/reasons-for-modification`,
  });
}

export function getCategoriesHotel({ lang, client_id }) {
  return requestAurora({
    method: 'GET',
    url: `api/client_hotels/classes`,
    params: {
      lang: lang,
      client_id: client_id,
    },
  });
}

export function getDestiniesByClient({ client_id }) {
  return requestAurora({
    method: 'GET',
    url: `services/hotels/destinations`,
    params: {
      client_id: client_id,
    },
  });
}

export function getFilterSearchHotels(params) {
  const url = params.simulation ? `services/hotels/available` : `services/hotels/available/quote`;

  return requestAurora({
    method: 'POST',
    url: url,
    data: params,
  });
}

export function getFilterSearchServices(params) {
  const url = params.simulation ? `services/available` : `services/available`;

  return requestAurora({
    method: 'POST',
    url: url,
    data: params,
  });
}

export function updateSchedule(data) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: SCHEDULE_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          access_token: getAccessToken(),
          type: data.type,
          object_id: data.id,
          itinerary_id: data.itinerary_id,
          file_number: data.file_number,
          new_start_time: data.new_start_time,
          new_departure_time: data.new_departure_time,
          frequency_code: data.frequency_code,
          frequency_name: data.frequency_name,
          flag_ignore_duration: data.flag_ignore_duration,
          schedule: data?.schedule ?? [],
        },
      ],
    },
  });
}

export function updateDate({ type, fileId, itineraryId, serviceId, date }) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: DATE_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          type: type,
          file_id: fileId,
          itinerary_id: itineraryId,
          serviceId: serviceId,
          date: date,
        },
      ],
    },
  });
}

export function saveFilePassengers(data) {
  return request({
    method: 'PUT',
    url: `files/${data.file_id}/passengers`,
    data: {
      passengers: data.passengers,
    },
  });
}

export function saveFileAccommodations(data) {
  return request({
    method: 'PUT',
    url: `files/${data.file_id}/accommodations`,
    data: {
      type: data.type,
      type_id: data.type_id,
      passengers: data.passengers,
    },
  });
}

export function updateFile(data) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: DEFAULT_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          access_token: getAccessToken(),
          file_id: data.id,
          file_number: data.file_number,
          description: data.description,
          lang: data.lang,
        },
      ],
    },
  });
}

export function updateAccommodations(data) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.access_token = getAccessToken();

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: ACCOMMODATIONS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [data],
    },
  });
}

export function updateAmounts(data) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.access_token = getAccessToken();
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: AMOUNTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [data],
    },
  });
}

export function addItinerary(data) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.access_token = getAccessToken();
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: ADD_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: 'files', // De qué servicio / sistema es enviado el mensaje
        notify: ['lsv@limatours.com.pe'],
      },
      payload: [data],
    },
  });
}

export function deleteItem(data) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.userName = getUserName();
  data.access_token = getAccessToken();
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: CANCELLATION_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [data],
    },
  });
}

export function getFileStatusReasons() {
  return request({
    method: 'GET',
    url: `status-reasons`,
  });
}

export function getFileStatusReasonsCancellation() {
  return request({
    method: 'GET',
    url: `reasons-for-cancellation`,
  });
}

export function cancelFile(data) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.access_token = getAccessToken();
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: CANCEL_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [data],
    },
  });
}

export function activateFile(fileId, params, status_reason_id = 4) {
  params.status_reason_id = status_reason_id;
  params.file_id = fileId;
  params.userId = getUserId();
  params.userCode = getUserCode();
  params.access_token = getAccessToken();

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: ACTIVATE_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [params],
    },
  });
}

export function fetchAllProviders(object_code) {
  return requestNode({
    method: 'GET',
    url: `providers/${object_code}`,
  });
}

export function verifyQuoteA2() {
  return request({
    method: 'POST',
    url: `files/quote/board`,
  });
}

export function sendQuoteA2(fileId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/quote/board`,
    data: params,
  });
}

export function passengerDownload({ fileId }) {
  return request({
    method: 'GET',
    url: `files/${fileId}/passenger-download`,
    responseType: 'blob',
  });
}

export function passengerDownloadAmadeus({ fileId }) {
  return request({
    method: 'GET',
    url: `files/${fileId}/passenger-download-amadeus`,
    responseType: 'blob',
  });
}

export function passengerDownloadPerurail({ fileId }) {
  return request({
    method: 'GET',
    url: `files/${fileId}/passenger-download-perurail`,
    responseType: 'blob',
  });
}

export function updateAllPassengers({ fileId, fileNumber, data }) {
  const params = {
    userId: getUserId(),
    userCode: getUserCode(),
    file_id: fileId,
    file_number: fileNumber,
    passengers: data,
  };

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: PASSENGERS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [params],
    },
  });
}

export function getPassengers({ fileId }) {
  return request({
    method: 'GET',
    url: `direct/files/${fileId}/passengers`,
  });
}

export function handleStoreRepository({ fileNumber, data }) {
  for (const pax of data) {
    if (pax.document_url != null && pax.document_url != '') {
      let new_files = {
        name: pax.document_url,
        file_url: pax.document_url,
      };

      let params = {
        user: getUserId(),
        files: [new_files],
        nroref: fileNumber,
        type_id: 7,
        information_aditional: '',
        flag_hide: 0,
        passengers: [pax.id],
      };

      return requestAurora({
        method: 'POST',
        url: `api/passenger_files`,
        data: params,
      });
    }
  }
}

export function createBasicFile(params) {
  return request({
    method: 'POST',
    url: `files/create-basic-file`,
    data: params,
  });
}

export function cloneBasicFile(fileId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/clone-file`,
    data: params,
  });
}

export function getFileCategories() {
  return request({
    method: 'GET',
    url: `files/categories`,
  });
}

export function getReasonStatement() {
  return request({
    method: 'GET',
    url: `files/reason-statement`,
  });
}

export function getMasterServices({ currentPage, perPage, type_service, filter }) {
  // Construir los parámetros de consulta
  const queryParams = new URLSearchParams();

  if (currentPage) queryParams.append('page', currentPage.toString());
  if (perPage) queryParams.append('per_page', perPage.toString());
  if (type_service) queryParams.append('type_service', type_service);
  if (filter) queryParams.append('filter', filter);

  return request({
    method: 'GET',
    url: `master-services?${queryParams.toString()}`, // Incluir los parámetros de consulta en la URL
  });
}

export function updateQuantityPassengers(object_id, params) {
  return request({
    method: 'PUT',
    url: `files/itineraries/${object_id}/number-of-passengers`,
    data: params,
  });
}

export function getAllModifyPaxs(file_id) {
  return request({
    method: 'GET',
    url: `files/${file_id}/passenger-modify-paxs`,
  });
}

export function saveServiceTemporary(params) {
  return request({
    method: 'POST',
    url: `temporary-service`,
    data: params,
  });
}

export function getCommunicationsTemporary(fileId, serviceId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries/${serviceId}/communication-temporary-service`,
    data: params,
  });
}

export function getSuppliersByCodes(params) {
  return requestNode({
    method: 'POST',
    url: `providers/codes`,
    data: params,
  });
}

export function updateCommunicationsTemporary(fileId, serviceId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries/${serviceId}/communication-temporary-service-type`,
    data: params,
  });
}

export function addServiceMaster(serviceId, params) {
  return request({
    method: 'POST',
    url: `files/itineraries/${serviceId}/services`,
    data: params,
  });
}

export function deleteServiceMaster(serviceId, params) {
  return request({
    method: 'DELETE',
    url: `files/itineraries/${serviceId}/services`,
    data: params,
  });
}

export function sendCommunicationMasterService(supplier_emails, type, attachments, html) {
  console.log(supplier_emails);
  let subject = '';
  if (type === 'cancellation') {
    subject = 'Cancelación de reserva';
  } else if (type === 'reservations') {
    subject = 'Solicitud de reserva';
  } else if (type === 'modification') {
    subject = 'Reserva modificada';
  }
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: `sqs-notifications-${STAGE}`, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: ['jgq@limatours.com.pe'],
      },
      payload: [
        {
          access_token: getAccessToken(),
          module: 'files',
          subject: subject,
          to: ['jgq@limatours.com.pe'],
          body: html,
          attachments: attachments,
        },
      ],
    },
  });
}

export function fetchServiceInformation(object_id, lang, date_out, paxs) {
  return requestAurora({
    method: 'GET',
    url: `api/service/${object_id}/moreDetails?lang=${lang}&date_out=${date_out}&total_pax=${paxs}`,
  });
}

export function fetchHotelInformation(object_id, lang) {
  return requestAurora({
    method: 'GET',
    url: `/services/hotel/${object_id}?lang=${lang}`,
  });
}

export function saveServiceTemporaryToFile(fileServiceId, params) {
  return request({
    method: 'POST',
    url: `files/${fileServiceId}/itineraries/${fileServiceId}/associate-temporary-service`,
    data: params,
  });
}

export function updateCategoriesFile(fileId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/categories`,
    data: params,
  });
}

export function searchCommunicationsCancellation(fileId, serviceId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries/${serviceId}/communication-service-cancellation`,
    data: params,
  });
}

export function searchCommunicationsNew(type, fileId, fileItineraryId, params) {
  const url =
    type === 'new'
      ? `files/${fileId}/itineraries/communication-service-news`
      : `files/${fileId}/itineraries/${fileItineraryId}/communication-service-modify`;
  return request({
    method: 'POST',
    url,
    data: params,
  });
}

export function findMasterServices(codes) {
  return requestNode({
    method: 'POST',
    url: `services/codes`,
    data: {
      equivalences: codes,
    },
  });
}

export function putFlagRateProtected(fileId, itineraryId) {
  return request({
    method: 'PUT',
    url: `files/${fileId}/itineraries/${itineraryId}/view-protected-rate`,
    data: [],
  });
}

export function putFlagFileRateProtected(fileId) {
  return request({
    method: 'PUT',
    url: `files/${fileId}/view-protected-rate`,
    data: [],
  });
}

export function searchFileReports(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/reports`,
    params: [],
  });
}

export function putServiceNotes({ type, fileId, params }) {
  let url = `files/${fileId}/itineraries/communication-service-${type}-type`;

  return request({
    method: 'POST',
    url: url,
    data: params,
  });
}

export function cancelServiceMaster(serviceItineraryId, params) {
  return request({
    method: 'DELETE',
    url: `files/itineraries/${serviceItineraryId}/services`,
    data: params,
  });
}

export function fetchRoomingListData($fileId) {
  return request({
    method: 'GET',
    url: `files/${$fileId}/romming-list`,
  });
}

export function fetchItineraryDetailsData($fileId, lang = 'es') {
  return request({
    method: 'GET',
    url: `files/${$fileId}/itinerary-details`,
    params: {
      lang: lang,
    },
  });
}

export function putConfirmationCode(file_number, type, id, confirmation_code, itinerary_id) {
  console.log(getUserId(), getUserCode());
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: CONFIRMATION_CODE_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          file_number: file_number,
          itinerary_id: itinerary_id,
          type: type,
          id: id,
          code: confirmation_code,
        },
      ],
    },
  });
}

export function getFilterSearchTemporaryServices(params) {
  return request({
    method: 'GET',
    url: 'temporary-service',
    params: params,
  });
}

export function putStatusRQWL(type, object_id, params) {
  let url = '';

  if (type == 'unit') {
    url = `files/hotel-rooms/units/${object_id}/changes-rq-wl`;
  }

  if (type == 'composition') {
    url = `files/services/compositions/units/${object_id}/changes-rq-wl`;
  }

  return request({
    method: 'PUT',
    url: url,
    data: params,
  });
}

export function putStatusWLOK(type, object_id, confirmation_code) {
  let url = '';

  if (type == 'unit') {
    url = `files/hotel-rooms/units/${object_id}/changes-wl-code`;
  }

  if (type == 'composition') {
    url = `files/services/compositions/units/${object_id}/changes-wl-code`;
  }

  return request({
    method: 'PUT',
    url: url,
    data: {
      code: confirmation_code,
    },
  });
}

export function saveServiceMaskToFile(fileId, params) {
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries`,
    data: params,
  });
}

export function fetchStatements(fileId) {
  return request({
    method: 'GET',
    url: `/files/${fileId}/statement`,
    params: [],
  });
}

export function fetchStatementDetails(fileId) {
  return request({
    method: 'GET',
    url: `/files/${fileId}/statement/details`,
    params: [],
  });
}

export function findStatementChanges(fileId) {
  return request({
    method: 'GET',
    url: `files/${fileId}/show-changes-statement`,
    params: [],
  });
}

export function updateStatementItineraries({ fileId, reasonId, otherReason, itineraries }) {
  return request({
    method: 'POST',
    url: `files/${fileId}/update-itinerary-add-statement`,
    data: {
      file_statement_reason_modification_id: reasonId, // int , NULL
      file_statement_reason_modification_others: otherReason,
      itineraries: itineraries,
    },
  });
}

export function fetchLogsAWS(fileId, params) {
  return request({
    method: 'GET',
    url: `/files/${fileId}/notification-aws-logs`,
    params: params,
  });
}

export function createCreditNote(fileId, details) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: STATEMENT_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          access_token: getAccessToken(),
          type: 'credit_note',
          file_id: fileId,
          params: {
            details: details,
          },
        },
      ],
    },
  });
}

export function createDebitNote(fileId, details) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: STATEMENT_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          access_token: getAccessToken(),
          type: 'debit_note',
          file_id: fileId,
          params: {
            details: details,
          },
        },
      ],
    },
  });
}

export function putUpdateStatement(fileId, deadline, details, restore) {
  localStorage.setItem('user_search_statement_changes', getUserCode());

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: STATEMENT_SQS_AMAZON, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: 'A3 && Informix', // A qué servicio se dirige el mensaje
        user: getUserId(), // Usuario que envía el mensaje
        service: DEFAULT_SERVICE, // De qué servicio / sistema es enviado el mensaje
        notify: [DEFAULT_EMAIL_NOTIFY],
      },
      payload: [
        {
          userId: getUserId(),
          userCode: getUserCode(),
          access_token: getAccessToken(),
          type: 'statement',
          file_id: fileId,
          params: {
            deadline: deadline,
            restore: restore,
            details: details,
          },
        },
      ],
    },
  });
}

export function importFileStella(file) {
  return request({
    method: 'POST',
    url: `/files/save-files-stela`,
    data: [file],
  });
}

export function filterServicesFrequences({ codes }) {
  return requestNode({
    method: 'POST',
    url: `/services/frequences`,
    data: {
      codes: codes,
    },
  });
}

export function filterServicesSchedules({ codes }) {
  return requestAurora({
    method: 'POST',
    url: `/api/services/schedules/group`,
    data: {
      codes: codes,
    },
  });
}

export function filterServicesGroups({ codes }) {
  return requestNode({
    method: 'POST',
    url: `/services/groups`,
    data: {
      codes: codes,
    },
  });
}

export function filterGroupSchedule({ code, group_code }) {
  return requestNode({
    method: 'POST',
    url: `/services/group_hours`,
    data: {
      code: code,
      group_code: group_code,
    },
  });
}

export function sendNotificationService({ file_id, itinerary_id, composition_id }) {
  return request({
    method: 'POST',
    url: `/files/${file_id}/itineraries/${itinerary_id}/communication-service-after-booking`,
    data: {
      composition_id: composition_id,
    },
  });
}

export function changeNotificationService({ composition_id }) {
  return request({
    method: 'PUT',
    url: `/files/services/compositions/${composition_id}/update_notification`,
    data: {},
  });
}

export function validateItinerary({ services, file_itinerary_id }) {
  return request({
    method: 'DELETE',
    url: `/files/itineraries/${file_itinerary_id}/services-validate`,
    data: {
      services,
      file_amount_reason_id: '', // esto se envia si existe una penalidad para saber quien asume la penalidad -- 12 no cobra penalidad
      executive_id: '', // esto se envia si existe una penalidad para saber quien asume la penalidad
      file_id: '', // esto se envia si existe una penalidad para saber quien asume la penalidad
      motive: '', // esto se envia si existe una penalidad para saber quien asume la penalidad
      status_reason_id: '',
    },
  });
}

export function searchFileServiceHotelCode(fileId, hotelCode) {
  return request({
    method: 'GET',
    url: `direct/files/${fileId}/reports/${hotelCode}`,
    params: [],
  });
}

export function fetchFileReservationProvider({ executive_code, hotel_id, client_id }) {
  return request({
    method: 'POST',
    url: `direct/files/reservation/providers`,
    data: {
      executive_code: executive_code,
      hotel_id: hotel_id,
      client_id: client_id,
    },
  });
}

export function generateMasterServices({ fileId }) {
  return request({
    method: 'GET',
    url: `files/${fileId}/generate-master-services`,
    params: [],
  });
}

export function validateFileErrors({ fileNumber }) {
  return requestAurora({
    method: 'GET',
    url: `api/reservations/${fileNumber}/process-error`,
    params: [],
  });
}

export function fetchFileBalanceAll({
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
    url: `files/balance`,
    params: {
      page: currentPage,
      per_page: perPage,
      filter: filter,
      filter_by: filterBy,
      filter_by_type: filterByType,
      executive_code: executiveCode,
      client_id: clientId,
      date_range: dateRange,
    },
  });
}
