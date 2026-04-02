import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number, file_id, itinerary_id, emails, user_id, user_code, user_name;

function buildParams(data, type) {
  return {
    executive_id: data.penality_executive_id || '',
    file_id: data.penality_file_id || '',
    motive: data.penality_motive || '',
    notas: data.notas ?? '',
    attachments: data.attachments ?? [],
    status_reason_id: data.status_reason_id || '',
    ...(type === 'hotel' && { rooms: data.rooms }),
    ...(type === 'service' && { services: data.services }),
  };
}

function sendCancellationEmail({ type, to, subject, body, attachments, objectId, hotelCode }) {  
  const payload = {
    mail_type: 'cancellation',
    mail_config_to: type,
    hotel_code: hotelCode,
    hotels: (type !== 'service') ? [hotelCode] : [],
    services: (type === 'service') ? [hotelCode] : [],
  };

  return sendRequest({
    url: `${process.env.BACKEND_URL_AMAZON}sqs/publish`,
    method: 'POST',
    data: {
      queueName: `sqs-notifications-${process.env.STAGE}`,
      metadata: {
        origin: "A3-Front",
        destination: "A3 && Informix",
        service: "files",
        notify: ["lsv@limatours.com.pe"],
      },
      payload: [{
        template: 'cancellation',
        data: JSON.stringify(payload),
        module: 'ms_files',
        submodule: 'cancel_itinerary',
        object_id: objectId,
        to,
        bcc: ['lsv@limatours.com.pe', 'jhg@limatours.com.pe'],
        subject,
        body,
        attachments
      }]
    }
  });
}

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
      const data = body.payload?.[0];
      if (!data) continue;

      const { type, flag_email = false, params_communication = false, confirmation } = data;

      user_id = data.userId ?? '';
      user_code = data.userCode ?? '';
      user_name = data.userName ?? '';
      file_number = data.file_number ?? '';
      file_id = data.file_id ?? '';
      itinerary_id = data.itinerary_id;

      let responseAurora = {};
      let processSuccessful = false;

      console.log("PAYLOAD:", JSON.stringify(data));
      const params = buildParams(data, type);

      if (type === 'hotel') {
        if (confirmation) {
          const renderResponse = await sendRequest({
            url: `${process.env.BACKEND_URL_NEW_AURORA}files/itineraries/${itinerary_id}/communication-hotel-cancellation`,
            method: 'POST',
            data: params,
          });

          response = renderResponse.data;
          console.log("RENDER CORREO:", response);

          const hotel_contacts = {
            ...response.data.hotel_contacts,
            ...data.emails,
          };

          if (response.success) {
            Object.assign(data, {
              flag_email: true,
              executive_emails: response.data.executive_email,
              hotel_emails: hotel_contacts,
              hotel_html: response.data.html.hotel,
              executive_html: response.data.html.executive,
              subject: response.data.subject,
            });
          } else {
            Object.assign(data, {
              flag_email: false,
            });
          }
        }

        responseAurora = await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/itineraries/${itinerary_id}/rooms?flag_lambda=1`,
          method: 'DELETE',
          data: params,
        });

        processSuccessful = true;
      }

      if (type === 'service' || type === 'service-mask') {
        emails = [];

        if(flag_email === 'cancellation' && params_communication) 
        {
          const commUrl = `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/communication-service-cancellation`;
      
          const responseCommunication = await sendRequest({
            url: commUrl,
            method: 'POST',
            data: params_communication,
          });

          console.log("PARAMS: ", JSON.stringify(params_communication));
          console.log("RESPONSE COMMUNICATION: ", JSON.stringify(responseCommunication.data))

          if (responseCommunication.data?.success)
          {
            const cancellations = responseCommunication.data.data.cancellation;

            for (const item of cancellations) {
              emails.push({
                cancellation: item,
                mail_type: 'cancellation',
                mail_config_to: '',
                hotel_code: data.code,
                hotels: [],
                services: [data.code],
              });
            }
          }
        }

        responseAurora = await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/itineraries/${itinerary_id}/services?flag_lambda=1`,
          method: 'DELETE',
          data: params,
        });

        processSuccessful = true;
      }

      if (type === 'flight') {
        responseAurora = await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${data.file_id}/itineraries/${itinerary_id}?flag_lambda=1`,
          method: 'DELETE',
        });

        processSuccessful = true;
      }

      console.log("RESPONSE AURORA:", JSON.stringify(responseAurora.data));

      if (processSuccessful && responseAurora.data?.success) {
        const stellaData = responseAurora.data.data.stella;
        const stellaEndpoint = responseAurora.data.data.endpoint;
        const stellaMethod = responseAurora.data.data.method;

        // Eliminación de NOTAS..
        const paramsNotes = {
          "created_by" : user_id,
          "created_by_code" : user_code,
          "created_by_name" : user_name
        };

        await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/itineraries/${itinerary_id}/delete-note`,
          method: 'DELETE',
          data: paramsNotes,
        });

        if (type === 'hotel') {
          const hyperguest = responseAurora.data.data.hyperguest ?? false;
          const hotelCode = stellaData.datahtl?.[0]?.codigoHotel;
          const objectId = stellaData.datapla?.nroref;

          if (confirmation && data.flag_email && hotelCode && objectId) {
            await Promise.all([
              sendCancellationEmail({
                type: 'executive',
                to: data.executive_emails,
                subject: data.subject,
                body: data.executive_html,
                attachments: data.attachments,
                objectId,
                hotelCode
              }),
              sendCancellationEmail({
                type: 'hotel',
                to: data.hotel_emails,
                subject: data.subject,
                body: data.hotel_html,
                attachments: data.attachments,
                objectId,
                hotelCode
              })
            ]);
          }

          await sendRequest({
            url: `${process.env.BACKEND_URL_FILES_ONEDB}files/${file_number}/hotel`,
            method: 'DELETE',
            data: stellaData,
          });

          const tokenHyperguest = process.env.TOKEN_HYPERGUEST ?? '-';

          if (hyperguest && tokenHyperguest && tokenHyperguest !== '-') {
            await sendRequest({
              url: `${process.env.BACKEND_URL_HYPERGUEST}${hyperguest.url}`,
              method: hyperguest.method,
              data: hyperguest.params,
              headers: {
                'Authorization': `Bearer ${tokenHyperguest}`,
                'Content-Type': 'application/json',
              },
              useCognito: false
            });
          }
        }

        if(type === 'service' || type === 'service-mask')
        {
          const emailPromises = emails.map((item) =>
            sendCancellationEmail({
              type: 'service',
              to: item.cancellation.supplier_emails,
              subject: `Anulación de servicio - ${item.cancellation.supplier_name}`,
              body: item.cancellation.html,
              attachments: item.attachments ?? [],
              objectId: file_number,
              hotelCode: item.hotel_code,
            })
          );
          
          await Promise.all(emailPromises);

          await sendRequest({
            url: stellaEndpoint,
            method: stellaMethod,
            data: stellaData,
          });
        }

        await notifyAllClients({
          success: true,
          type: 'update_file',
          action: 'delete',
          message: 'files.notification.update_file',
          description: 'files.notification.cancel_itinerary_success',
          file_id: file_id,
          file_number: file_number,
          itinerary_id: itinerary_id,
          user_id: user_id,
          user_code: user_code,
        });
      }
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_file',
      action: 'delete',
      file_id: file_id,
      file_number: file_number,
      itinerary_id: itinerary_id,
      message: 'files.notification.cancel_itinerary',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
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

    console.log("Final Response:", finalResponse);
    return finalResponse;
  }
};