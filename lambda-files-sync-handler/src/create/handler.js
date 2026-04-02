import { sendRequest, showError, sleep } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number = 0;

// Función para validar y reparar JSON
const validateJSON = (json) => {
  try {
    return typeof json === 'string' ? JSON.parse(json) : json;
  } catch (err) {
    const fixedJsonString = JSON.stringify(json).replace(/\\(?!["\\])/g, '\\\\');
    return JSON.parse(fixedJsonString);
  }
};

// Función para manejar la reserva
const handleReservation = async (reservationId) => {
  const url = `${process.env.BACKEND_URL_AURORA}reservations/${reservationId}/show_itinerary/1`;
  const response = await sendRequest({
    method: 'GET',
    url: url,
    headers: { responseType: 'json' },
    useCognito: false,
  });

  const responseAurora = response.data.data;
  file_number = Number(responseAurora.file_code);

  console.log("Reservation URL:", url);
  console.log("Response Aurora: ", JSON.stringify(responseAurora));

  if(responseAurora.entity === "Quote")
  {
    // Generar la relación de pedidos con files..
    const queueName = `sqs-orders-sync-file-${process.env.STAGE}`;
    const urlAmazon = `${process.env.BACKEND_URL_AMAZON}sqs/publish`;

    const paramsEmail = {
      queueName,
      metadata: {
        origin: 'files_ms',
        destination: 'orders_ms',
        user: responseAurora.create_user.code ?? 'LSV',
        service: 'orders_ms',
        notify: ['lsv@limatours.com.pe'],
      },
      payload: [
        {
          fileNumber: responseAurora.file_code,
          quoteId: responseAurora.object_id,
          orderNumber: responseAurora.order_number,
          createdAt: responseAurora.created_at,
        },
      ],
    };

    await sendRequest({
      url: urlAmazon,
      method: 'POST',
      data: paramsEmail,
    });
  }

  return responseAurora;
};

// Función para manejar el tipo OTS
const handleOts = (payload) => {
  console.log("OTS Payload:", JSON.stringify(payload));
  return payload.data;
};

// Función para procesar un solo record
const processRecord = async (record) => {
  const body = validateJSON(record.body);
  const payload = body.payload[0];
  const { type, reservation_id } = payload;

  let data;

  switch (type) {
    case 'reservation':
      data = await handleReservation(reservation_id);
      break;
    case 'ots':
      data = handleOts(payload);
      break;
    default:
      throw new Error(`Unsupported record type: ${type}`);
  }

  const params = validateJSON(data);
  console.log("Data Sent to Aurora 3:", JSON.stringify(params));

  // Guardar los datos en Aurora
  const responseAurora = await sendRequest({
    method: 'POST',
    url: `${process.env.BACKEND_URL_NEW_AURORA}files`,
    data: params,
  });

  console.log("REPUESTA A3: ", JSON.stringify(responseAurora.data));

  // Manejo adicional para reservas
  if(responseAurora.data.success) {
    await notifyAllClients({
      success: true,
      type: 'update_file',
      action: 'new',
      message: 'files.notification.update_file',
      description: 'files.notification.create_success',
      file_number: parseInt(file_number),
    });

    const file_id = responseAurora.data?.file_id ?? null;

    if (type === 'reservation') {
      if(file_id) {
        // Colocando el file en estado Processing..
        /*
        await sendRequest({
          method: "POST",
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/processing`,
        });

        await notifyAllClients({
          success: true,
          action: 'status',
          type: 'update_file',
          file_id: parseInt(file_id),
        });
        */
      }

      const itineraryUrl = `${process.env.BACKEND_URL_AURORA}reservations/${reservation_id}/update_itinerary`;
      const response = await sendRequest({
        method: 'GET',
        url: itineraryUrl,
        useCognito: false,
      });

      console.log("REPUESTA DE ACTUALIZACIÓN A2: ", JSON.stringify(response.data));

      if(file_id) {
        // Desbloqueando el FILE..
        /*
        await sendRequest({
          method: "POST",
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/unlock/${file_number}?flag_lambda=1`,
          data: []
        });

        await notifyAllClients({
          success: true,
          action: 'status',
          type: 'update_file',
          file_number: parseInt(file_number),
        });
        */
      }
    }
  }

  return responseAurora.data;
};

// Controlador principal
export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const recordResponse = await processRecord(record);
      response = JSON.stringify(recordResponse);
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_file',
      action: 'new',
      message: (file_number > 0) ? 'files.notification.update_file' : 'files.notification.create_file',
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      description: error.message,
      file_number: file_number ?? 0,
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