import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number, itinerary_id, user_id, user_code;

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      let data = body.payload[0];
      let response_aurora = '';
      let response_informix = '';

      user_id = data.user_id;
      user_code = data.user_code;
      file_number = data.file_number;
      itinerary_id = data.itinerary_id;

      const params = {
        file_amount_reason_id: data.file_amount_reason_id,
        new_amount: data.new_amount,
        user_id: data.user_id ?? 1,
      };

      console.log("PARAMS: ", params);

      if (data.type === 'service') {
        response_aurora = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/services/${data.object_id}/amount?flag_lambda=1`,
          data: params,
        });
      }

      if (data.type === 'room') {
        response_aurora = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/hotel-rooms/${data.object_id}/amount?flag_lambda=1`,
          data: params,
        });
      }

      response = response_aurora.data;
      console.log("RESPONSE AURORA: ", JSON.stringify(response));

      if (response.success) {
        const params_informix = response.data.stela;
        console.log("PARAMS INFORMIX: ", params_informix);

        response_informix = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_FILES_ONEDB}files/${file_number}/services/amounts`,
          data: params_informix,
        });

        console.log("RESPUESTA FILES ONE DB: ", JSON.stringify(response_informix.data));
        response = JSON.stringify(response_informix.data);

        await notifyAllClients({
          success: true,
          type: 'update_itinerary',
          action: 'single',
          message: 'files.notification.update_itinerary',
          description: 'files.notification.amounts_success',
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
      type: 'update_itinerary',
      action: 'single',
      message: 'files.notification.amounts',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      file_number: file_number,
      itinerary_id: itinerary_id,
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