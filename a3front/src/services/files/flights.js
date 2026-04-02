import { getUserCode, getUserId } from '../../utils/auth';
import request from '../../utils/request';
import requestAurora from '@/utils/requestAurora.js';
import requestSQS from '../../utils/requestSQS';

const STAGE = import.meta.env.VITE_APP_ENV;

const FLIGHTS_SQS_AMAZON = `sqs-files-sync-flights-${STAGE}`;
const DEFAULT_EMAIL_NOTIFY = 'lsv@limatours.com.pe';
const DEFAULT_SERVICE = 'files';

export function addFlightItem({ fileId, fileItineraryId, data }) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.file_id = fileId;
  data.file_itinerary_id = fileItineraryId;
  data.type = 'new-flight';

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries/${fileItineraryId}/flight`,
    data: data,
  });
  */
}
export function removeFlightItem({ fileId, fileItineraryId }) {
  const data = {
    userId: getUserId(),
    userCode: getUserCode(),
    file_id: fileId,
    file_itinerary_id: fileItineraryId,
    type: 'remove-flight',
  };

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'DELETE',
    url: `files/${fileId}/itineraries/${fileItineraryId}`,
  });
  */
}

export function removeSubFlightItem({ fileId, fileItineraryId, itemId }) {
  const data = {
    userId: getUserId(),
    userCode: getUserCode(),
    file_id: fileId,
    file_itinerary_id: fileItineraryId,
    item_id: itemId,
    type: 'remove-flight-item',
  };

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'DELETE',
    url: `files/${fileId}/itineraries/${fileItineraryId}/flight/${itemId}`,
  });
  */
}

export function storeSubFlightItem({ fileId, fileItineraryId, data }) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.file_id = fileId;
  data.file_itinerary_id = fileItineraryId;
  data.type = 'new-flight-item';

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'POST',
    url: `files/${fileId}/itineraries/${fileItineraryId}/flight`,
    data: data,
  });
  */
}

export function updateSubFlightItem({ fileId, fileItineraryId, itemId, data }) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.file_id = fileId;
  data.file_itinerary_id = fileItineraryId;
  data.item_id = itemId;
  data.type = 'update-flight-item';

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'PUT',
    url: `files/${fileId}/itineraries/${fileItineraryId}/flight/${itemId}`,
    data: data,
  });
  */
}

export function getAirlines({ query }) {
  return requestAurora({
    method: 'GET',
    url: `api/flights/airlines`,
    params: {
      query: query,
    },
  });
}

export function getFlightInformation({ flight_number }) {
  return request({
    method: 'POST',
    url: `files/flight/information`,
    data: { flight_number },
  });
}

export function updateFlightItem({ file_id, flight_id, data }) {
  return request({
    method: 'PUT',
    url: `/files/${file_id}/itineraries/${flight_id}/flight/city-iso`,
    data: data,
  });
}

export function updateCityIso({ fileId, fileItineraryId, data }) {
  data.userId = getUserId();
  data.userCode = getUserCode();
  data.file_id = fileId;
  data.file_itinerary_id = fileItineraryId;
  data.type = 'update-city-iso';

  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: FLIGHTS_SQS_AMAZON, // Cola dónde será dirigido el mensaje
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

  /*
  return request({
    method: 'PUT',
    url: `files/${fileId}/itineraries/${fileItineraryId}/flight/city-iso`,
    data: data,
  });
  */
}

export function validationFlight({ data = { city_isos: [] } }) {
  return request({
    method: 'POST',
    url: `files/validation/flight/countries/isos`,
    data: data,
  });
}
