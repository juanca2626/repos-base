import { sendRequest, showError } from '../helpers/request.js';

// Utilidad para loguear errores uniformemente
const logAndThrowError = (context, err) => {
  const error = showError(err);
  console.error(`Error en ${context}:`, error.message);
  throw new Error(`${context}: ${error.message}`);
};

const renderEmail = async (type, data) => {
  try {
    const urlMap = {
      new: `${process.env.BACKEND_URL_NEW_AURORA}files/${data.file_id}/communication-hotel-new-reserva`,
      modification: `${process.env.BACKEND_URL_NEW_AURORA}files/itineraries/${data.file_itinerary_id}/communication-hotel-modification-reserva`,
    };

    const url = urlMap[type];
    if (!url) return null;

    data.reservation_delete = data?.cancellation?.rooms;

    return await sendRequest({
      url,
      method: 'POST',
      data,
    });
  } catch (err) {
    logAndThrowError('renderEmail', err);
  }
};

const sendNotificationEmail = async (recipient, data, html, subject, attachments) => {
  try {
    const emailData = {
      mail_type: 'reservation',
      mail_config_to: recipient,
      hotel_code: data.hotel_code,
      hotels: [data.hotel_code],
      services: [],
    };

    const paramsEmail = {
      queueName: `sqs-notifications-${process.env.STAGE}`,
      metadata: {
        origin: 'A3-Front',
        destination: 'A3 && Informix',
        user: '',
        service: 'files',
        notify: ['lsv@limatours.com.pe'],
      },
      payload: [
        {
          template: 'communication-hotel',
          data: JSON.stringify(emailData),
          module: 'ms_files',
          submodule: data.type,
          object_id: data.file_number,
          to: data.emails,
          cc: data?.cc ?? [],
          bcc: (recipient === 'hotel') ? ['lsv@limatours.com.pe', 'jhg@limatours.com.pe'] : [],
          subject,
          body: html,
          replyTo: [],
          attachments,
        },
      ],
    };

    const urlAmazon = `${process.env.BACKEND_URL_AMAZON}sqs/publish`;

    return await sendRequest({
      url: urlAmazon,
      method: 'POST',
      data: paramsEmail,
    });
  } catch (err) {
    logAndThrowError('sendNotificationEmail', err);
  }
};

const notifyRecipients = async (data, emailData, attachments) => {
  try {
    const { html, subject, hotel_contacts, clients_email, executive_email } = emailData.data;

    const notifications = [
      { recipient: 'hotel', emails: hotel_contacts, body: html.hotel },
      { recipient: 'client', emails: clients_email, body: html.client },
      { recipient: 'executive', emails: executive_email, body: html.executive },
    ];

    for (const { recipient, emails, body } of notifications) {
      console.log("EMAILS RECIPIENT: ", emails);
      console.log("DATA EMAILS: ", data.emails);

      if(recipient === 'hotel')
      {
        data.emails = {
          ...data.emails,
          ...emails,
        };
      }
      else
      {
        data.emails = emails;
      }
      
      console.log(`📩 Enviando notificación a ${recipient}...`);
      await sendNotificationEmail(recipient, data, body, subject, attachments);
    }
  } catch (err) {
    logAndThrowError('notifyRecipients', err);
  }
};

const cancelItinerary = async (data) => {
  try {
    const dataCancellation = {
      ...data.cancellation,
      type: 'hotel',
      flag_email: (data.cancellation.type_from === 'modification') ? false : true,
    };
    console.log("HOTEL CANCEL PARAMS: ", JSON.stringify(dataCancellation));

    const paramsCancellation = {
      queueName: `sqs-files-sync-cancellation-${process.env.STAGE}`,
      metadata: {
        origin: 'A3-Front',
        destination: 'A3 && Informix',
        user: '',
        service: 'files',
        notify: ['lsv@limatours.com.pe'],
      },
      payload: [
        dataCancellation,
      ],
    };

    const urlAmazon = `${process.env.BACKEND_URL_AMAZON}sqs/publish`;

    return await sendRequest({
      url: urlAmazon,
      method: 'POST',
      data: paramsCancellation,
    });
  } catch (err) {
    logAndThrowError('cancelItinerary', err);
  }
};

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
      const data = body.payload[0];

      console.log('Procesando data:', data);

      const emailResponse = await renderEmail(data.type, data);

      if (emailResponse?.data?.success) {
        if (typeof data.cancellation.confirmation !== 'undefined') {
          console.log("Hay cancelación de hotel previo...");
          await cancelItinerary(data);
        }

        const attachments = data.attachments;
        await notifyRecipients(data, emailResponse.data, attachments);
      } else {
        response = 'No se pudo renderizar el correo electrónico';
        code = 500;
      }
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;
  } finally {
    const finalResponse = {
      statusCode: code,
      body: response,
    };
    console.log('✅ Final Response:', finalResponse);
    return finalResponse;
  }
};