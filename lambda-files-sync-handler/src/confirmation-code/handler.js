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
      const data = body.payload[0];
      const type = data.type;
      const id = data.id;

      user_id = data.userId;
      user_code = data.userCode;
      file_number = data.file_number;
      itinerary_id = data.itinerary_id;

      let params = {
        "code": data.code,
      }

      let url = '';

      if (type == 'room') {
        url = `hotel-rooms`;
      }
    
      if (type == 'room-unit') {
        url = `hotel-rooms/units`;
      }
    
      if (type == 'composition') {
        url = `services/compositions`;
      }
      
      let response_aurora = await sendRequest({
        method: 'PUT',
        url: `${process.env.BACKEND_URL_NEW_AURORA}files/${url}/${id}/confirmation-code?flag_lambda=1`,
        data: params,
      });
      
      response = response_aurora.data;

      if(response.success)
      {
        const reservationResponse = await sendRequest({
          method: 'POST',
          url: response.data.endpoint ?? `${process.env.BACKEND_URL_FILES_ONEDB}services/${data.file}/codcfm-hotel`,
          data: response.data.stella,
        });

        console.log("Respuesta..", reservationResponse);
        response = JSON.stringify(reservationResponse.data);

        await notifyAllClients({
          success: true,
          type: 'update_itinerary',
          action: 'confirmation-code',
          itinerary_id: itinerary_id,
          message: 'files.notification.update_itinerary',
          description: 'files.notification.confirmation_code_success',
          file_number: file_number,
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
      action: 'confirmation-code',
      itinerary_id: itinerary_id,
      file_number: file_number,
      message: 'files.notification.confirmation_code',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
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
    };

    console.log("Final Response: ", finalResponse);
    return finalResponse;
  }
}