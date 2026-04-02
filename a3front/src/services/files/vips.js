import { getUserCode, getUserId } from '../../utils/auth';
import request from '../../utils/request';
import requestSQS from '../../utils/requestSQS';

const STAGE = import.meta.env.VITE_APP_ENV;
const DEFAULT_ENTITY = 'file';
const DEFAULT_SQS_AMAZON = `sqs-files-sync-vip-${STAGE}`;
const DEFAULT_EMAIL_NOTIFY = 'lsv@limatours.com.pe';
const DEFAULT_SERVICE = 'files';

export function fetchVips() {
  return request({
    baseURL: window.API,
    method: 'GET',
    url: 'vips',
    params: {},
  });
}

export function addVipAndFileRelated({ fileId, vipId }) {
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
          type: 'save',
          file_id: fileId,
          vip_id: vipId,
        },
      ],
    },
  });
}

export function changeVipAndFileRelated({ fileId, vipId }) {
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
          type: 'save',
          file_id: fileId,
          vip_id: vipId,
        },
      ],
    },
  });
  /*
  return request({
    baseURL: window.AMAZON_SQS,
    method: 'PUT',
    url: `files/${fileId}/vips/${vipsId}`,
    data: {
      vip_id: vipId
    }
  })
  */
}

export function createVipRelated({ fileId, vipName }) {
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
          type: 'new',
          name: vipName,
          entity: DEFAULT_ENTITY,
          file_id: fileId,
        },
      ],
    },
  });
}

export function removeVipAndFileRelatedService({ fileId, vipId }) {
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
          type: 'delete',
          file_id: fileId,
          vip_id: vipId,
        },
      ],
    },
  });
}
