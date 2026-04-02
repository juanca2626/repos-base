import request from '../../utils/requestBrevo';
import requestSQS from '../../utils/requestSQS';

const STAGE = import.meta.env.VITE_APP_ENV;

export function searchData(params) {
  return request({
    method: 'GET',
    url: 'filter',
    params: params,
  });
}

export function sendNotification(params) {
  return requestSQS({
    method: 'POST',
    url: 'sqs/publish',
    data: {
      queueName: `sqs-notifications-${STAGE}`, // Cola dónde será dirigido el mensaje
      metadata: {
        origin: 'A3-Front', // De qué servicio / sistema es enviado el mensaje
        destination: params.destination ?? '', // A qué servicio se dirige el mensaje
        user: params.user, // Usuario que envía el mensaje
        service: params.service ?? 'files', // De qué servicio / sistema es enviado el mensaje
        notify: ['lsv@limatours.com.pe'],
      },
      payload: [
        {
          flag_send: params.flag_send ?? false, // Evitar que se envíen los correos..
          template: params.template, // Template generado en el S3
          data: JSON.stringify(params.data), // JSON de paramáteros para el template
          module: params.module ?? '', // ms_files
          submodule: params.submodule ?? '', // create files
          object_id: params.object_id ?? '', // ??
          to: params.to,
          cc: params.cc ?? [],
          bcc: params.bcc ?? [],
          subject: params.subject ?? '',
          body: params.body ?? '', // html ms_files
          replyTo: params.replyTo ?? [],
          attachments: params.attachments ?? [], // Enlaces S3
        },
      ],
    },
  });
}
