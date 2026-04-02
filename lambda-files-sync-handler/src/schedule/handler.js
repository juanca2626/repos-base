import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number, itinerary_id, user_id, user_code;

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try
  {
    for (const record of event.Records)
    {
      let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      let data = body.payload[0];
      let response_aurora = '';

      user_id = data.userId;
      user_code = data.userCode;
      file_number = data.file_number;
      itinerary_id = data.itinerary_id;

      if (data.new_start_time && !data.new_start_time.match(/^\d{2}:\d{2}:\d{2}$/)) {
        data.new_start_time += ':00';
      }

      if (data.new_departure_time && !data.new_departure_time.match(/^\d{2}:\d{2}:\d{2}$/)) {
        data.new_departure_time += ':00';
      }

      let params = {
        "start_time": `${data.new_start_time}`,
        "departure_time": `${data.new_departure_time}`,
        "flag_ignore_duration": `${data.flag_ignore_duration}`,
        "frecuency_code": `${data.frequency_code ?? ''}`,
        "frecuency_name": `${data.frequency_name ?? ''}`,
      }

      console.log("PARAMS: ", JSON.stringify(params));

      response_aurora = await sendRequest({
        method: 'PUT',
        url: `${process.env.BACKEND_URL_NEW_AURORA}files/${data.type}/${data.object_id}/schedule?flag_lambda=1`,
        data: params,
      });

      response = response_aurora.data;
      console.log("Respuesta A3: ", JSON.stringify(response));
      
      if(response.success)
      {
        const url = response.data.endpoint;
        const method = response.data.method;
        const params_informix = response.data.stela

        let response_informix = await sendRequest({
          method: `${method}`,
          url: `${url}`,
          data: params_informix,
        });

        response = JSON.stringify(response_informix.data);
        console.log("RESPONSE INFORMIX: ", JSON.stringify(response));

        await notifyAllClients({
          success: true,
          type: 'update_itinerary',
          action: 'single',
          message: 'files.notification.update_itinerary',
          description: 'files.notification.schedule_success',
          file_number: file_number,
          itinerary_id: itinerary_id,
          user_id: user_id,
          user_code: user_code,
        });
      }
    }
  }
  catch(err)
  {
    const error = showError(err);
    response = error.message;
    code = 500;
    
    await notifyAllClients({
      success: false,
      type: 'update_itinerary',
      action: 'single',
      message: 'files.notification.schedule',
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
  }
  finally
  {
    const finalResponse = {
      statusCode: code,
      body: response,
    }

    console.log("Response: ", finalResponse);
    return finalResponse;
  }
}