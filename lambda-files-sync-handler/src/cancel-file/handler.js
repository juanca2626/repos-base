import { sendRequest, showError } from "../helpers/request.js";
import { notifyAllClients } from "../helpers/websocketNotifier.js";

// Utilidad para enviar mensaje a SQS
const sendCancellation = (paramsSqs) =>
  sendRequest({
    method: "POST",
    url: `${process.env.BACKEND_URL_AMAZON}sqs/publish`,
    data: typeof paramsSqs === "string" ? JSON.parse(paramsSqs) : paramsSqs,
  });

// Utilidad para obtener un itinerario completo
const fetchItinerary = async (fileId, itineraryId) => {
  const response = await sendRequest({
    method: "GET",
    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${fileId}/itineraries/${itineraryId}`,
  });
  return response?.data?.data || {};
};

// Construye los parámetros a enviar al SQS en base al itinerario y datos generales
const buildParamsForItinerary = (itinerary, data, userId, userCode) => {
  const params = {
    userId,
    userCode,
    itinerary_id: itinerary.id,
    confirmation: itinerary.confirmation_status,
    file_id: data.file_id,
    file_number: data.file_number,
    type: itinerary.entity,
    notas: data.notas,
    attachments: data.attachments,
    status_reason_id: data.status_reason_id,
  };

  if (itinerary.entity === "hotel") {
    params.rooms = itinerary.rooms?.map((room) => ({
      id: room.id,
      units: room.units?.map((unit) => unit.id),
    })) || [];
  }

  if (itinerary.entity === "service") {
    params.services = itinerary.services?.map((service) => ({
      id: service.id,
      compositions: service.compositions?.map((comp) => comp.id),
    })) || [];
  }

  return params;
};

// MAIN HANDLER
export const handler = async (event, context) => {
  let statusCode = 200;
  let responseBody = "";
  
  let file_id, file_number, userId, userCode;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === "string" ? JSON.parse(record.body) : record.body;
      const data = body.payload?.[0];

      if (!data || !data.file_id || !data.userId || !data.userCode) {
        throw new Error("Invalid payload structure");
      }

      console.log("PARAMS:", JSON.stringify(data));
      file_id = data.file_id;
      file_number = data.file_number;
      userId = data.userId;
      userCode = data.userCode;

      // const { userId, userCode } = { userId: data.userId, userCode: data.userCode };

      // Obtener archivo con itinerarios
      const fileResponse = await sendRequest({
        method: "GET",
        url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}`,
      });

      const itineraries = fileResponse?.data?.data?.itineraries || [];

      const activeItineraries = itineraries.filter((it) => it.status === true || it.status === 1);

      if(activeItineraries.length > 0)
      {
        // Colocando el file en estado Processing..
        await sendRequest({
          method: "POST",
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/processing`,
        });

        await notifyAllClients({
          success: true,
          action: 'status',
          type: 'update_file',
          // message: 'Modificación de FILE',
          // description: 'El estado del FILE ha sido actualizado.',
          file_id: parseInt(file_id),
        });
      }

      const promises = activeItineraries.map(async (itinerary) => {
        const itineraryData = await fetchItinerary(file_id, itinerary.id);
        const params = buildParamsForItinerary(itineraryData, data, userId, userCode);

        if(itinerary.entity === "flight")
        {
          params.type = 'remove-flight';
          
          const paramsSqs = {
            queueName: `sqs-files-sync-flights-${process.env.STAGE}`,
            metadata: {
              origin: "A3-Front",
              destination: "A3 && Informix",
              user: userCode,
              service: "files",
              notify: ["lsv@limatours.com.pe"],
            },
            payload: [params],
          };
  
          return sendCancellation(paramsSqs);
        }

        else
        {
          const paramsSqs = {
            queueName: `sqs-files-sync-cancellation-${process.env.STAGE}`,
            metadata: {
              origin: "A3-Front",
              destination: "A3 && Informix",
              user: userCode,
              service: "files",
              notify: ["lsv@limatours.com.pe"],
            },
            payload: [params],
          };
  
          return sendCancellation(paramsSqs);
        }
      });

      responseBody = await Promise.all(promises);
    }
  } catch (err) {
    const error = showError(err);
    statusCode = 500;
    responseBody = error.message;

    console.error("ERROR:", error);

    await notifyAllClients({
      success: false,
      type: "update_file",
      file_id: file_id,
      file_number: file_number,
      message: 'files.notification.cancel_file',
      description: responseBody,
      stream_log:
        context.logStreamName && context.functionName
          ? `${context.logStreamName} (${context.functionName})`
          : "",
      url: error.url,
      data: error.data,
      user_id: userId,
      user_code: userCode,
    });
  }

  const finalResponse = {
    statusCode,
    body: responseBody,
  };

  console.log("Final Response:", finalResponse);
  return finalResponse;
};