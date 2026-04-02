import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number, file_id, user_id, user_code;

const processRecord = async (record, reservationUrl) => {
  const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
  const data = body.payload[0];
  let { type, flag_email, reservation_add } = data;

  user_id = data.userId;
  user_code = data.userCode;
  file_id = data.file_id;

  console.log("PARAMS: ", JSON.stringify(data));
  console.log("RESERVATION: ", JSON.stringify(reservation_add));

  if (type === 'hotel') {
    let files_ms_parameters = [];

    for(const reservation of reservation_add.reservations)
    {
      const item = {
        file_number: data.file_number,
        hotel_code: reservation.code,
        token_search: reservation.token_search,
        hotel_id: reservation.hotel_id,
        type: flag_email,
        lang: data.lang ?? 'es',
        file_id: file_id ?? null,
        file_itinerary_id: (flag_email === 'new') ? null : (data.file_itinerary_id ?? null),
        emails: reservation.emails,
        attachments: reservation.attachments,
        notas: reservation.notas,
        cancellation: data.cancellation ?? null,
      }

      files_ms_parameters.push(item);
    }

    reservation_add.files_ms_parameters = files_ms_parameters;
  }

  file_number = reservation_add.file_code;

  const reservationResponse = await sendRequest({
    method: 'post',
    url: reservationUrl,
    data: reservation_add,
    headers: {
      Authorization: `Bearer ${data.access_token}`,
      'Content-Type': 'application/json',
    },
    useCognito: false,
  });

  const response = reservationResponse.data;
  console.log("RESPUESTA RESERVATION: ", JSON.stringify(response));

  if(!response.success)
  {
    await notifyAllClients({
      success: false,
      type: 'update_file',
      action: 'new',
      file_number: file_number,
      message: 'files.notification.update_file',
      description: response.error.message,
      url: `post ${reservationUrl}`,
      data: reservation_add,
      user_id: user_id,
      user_code: user_code,
    });
  }
  else
  {
    if (type !== 'service') return;

    const queueName = `sqs-notifications-${process.env.STAGE}`;
    const urlAmazon = `${process.env.BACKEND_URL_AMAZON}sqs/publish`;

    for (const service of reservation_add.reservations_services) {
      if(typeof service.params_communication !== 'undefined')
      {
        const type_communication = service.params_communication.type;
        const commUrl = (type_communication === 'new') ? `files/${file_id}/itineraries/communication-service-news` : `files/${file_id}/itineraries/${service.params_communication.itinerary_id}/communication-service-modify`;

        try {
          const responseCommunication = await sendRequest({
            url: `${process.env.BACKEND_URL_NEW_AURORA}${commUrl}`,
            method: 'POST',
            data: service.params_communication,
          });

          console.log("RENDER CORREO: ", JSON.stringify(responseCommunication.data));

          if (responseCommunication.data?.success) {

            let items = [];

            if(type_communication === 'new')
            {
              items = responseCommunication.data.data.reservations;
            }
            else
            {
              items = responseCommunication.data.data.modification;
            }

            for (const item of items) {
              const emailData = {
                mail_type: 'reservation',
                mail_config_to: '',
                hotel_code: service.code,
                hotels: [],
                services: [service.code],
              };
  
              const paramsEmail = {
                queueName,
                metadata: {
                  origin: 'A3-Front',
                  destination: 'A3 && Informix',
                  user: '',
                  service: 'files',
                  notify: ['lsv@limatours.com.pe'],
                },
                payload: [
                  {
                    userId: user_id,
                    userCode: user_code,
                    template: 'communication-service',
                    data: JSON.stringify(emailData),
                    module: 'ms_files',
                    submodule: 'confirmation',
                    object_id: file_number,
                    to: item.supplier_emails,
                    cc: [],
                    bcc: [],
                    subject: `${(type_communication === 'new') ? 'Solicitud de Reserva' : 'Modificación de Reserva'} - ${item.supplier_name}`,
                    body: item.html,
                    replyTo: [],
                    attachments: item.attachments,
                  },
                ],
              };
  
              await sendRequest({
                url: urlAmazon,
                method: 'POST',
                data: paramsEmail,
              });
            }
          }

          if(type_communication !== 'new')
          {
            const paramsEmail = {
              queueName: `sqs-files-sync-cancellation-${process.env.STAGE}`,
              metadata: {
                origin: 'A3-Front',
                destination: 'A3 && Informix',
                user: '',
                service: 'files',
                notify: ['lsv@limatours.com.pe'],
              },
              payload: [
                {
                  userId: user_id,
                  userCode: user_code,
                  type: "service",
                  flag_email: false,
                  file_id: file_id,
                  file_number: file_number,
                  itinerary_id: service.params_communication.itinerary_id,
                  code: service.code,
                  services: params_cancellation,
                  access_token: data.access_token,
                },
              ],
            };
  
            await sendRequest({
              url: urlAmazon,
              method: 'POST',
              data: paramsEmail,
            });
          }
        } catch (err) {
          console.error(`Error procesando comunicaciones para service.code = ${service.code}`, err);
          // Puedes decidir si continuar o lanzar error
        }
      }
    }
  }

  return response;
};

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  const reservationUrl = `${process.env.BACKEND_URL_AURORA.replace('/api', '')}services/hotels/reservation/add`;

  try {
    for (const record of event.Records) {
      response = await processRecord(record, reservationUrl);
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_file',
      action: 'new',
      file_number: file_number,
      message: 'files.notification.update_file',
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      description: response,
      url: error.url,
      data: error.data,
      user_id: user_id,
      user_code: user_code,
    });
  } finally {
    const finalResponse = {
      statusCode: code,
      body: response,
    };
    console.log("Final Response: ", finalResponse);
    return finalResponse;
  }
};