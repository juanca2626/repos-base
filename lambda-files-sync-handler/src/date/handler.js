import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_id, itinerary_id, user_id, user_code, serviceId;

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
      file_id = data.file_id;
      itinerary_id = data.itinerary_id;
      serviceId = data.serviceId;
      const type = data.type ?? 'flight';

      let params = {
        "date": `${data.date}`,
      }

      console.log("PARAMS: ", JSON.stringify(params));

      if(type === 'flight')
      {
        response_aurora = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itinerary/${itinerary_id}/flight/date?flag_lambda=1`,
          data: params,
        });
      }

      if(type === 'master-service')
      {
        response_aurora = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/services/${serviceId}/date?flag_lambda=1`,
          data: params,
        });
      }
      

      response = response_aurora.data;
      console.log("Respuesta A3: ", JSON.stringify(response));
      
      if(response.success)
      {
        const url = response.data.endpoint;
        const method = response.data.method;
        const stella = response.data.stela;

        if(type === 'flight')
        {
          for(const data_stella of stella)
          {
            const response_informix = await sendRequest({
              method: `${method}`,
              url: `${url}`,
              data: data_stella,
            });
  
            response = JSON.stringify(response_informix.data);
            console.log("RESPONSE INFORMIX: ", JSON.stringify(response));
          }
        }

        if(type === 'master-service')
        {
          const response_informix = await sendRequest({
            method: `${method}`,
            url: `${url}`,
            data: stella,
          });

          response = JSON.stringify(response_informix.data);
          console.log("RESPONSE INFORMIX: ", JSON.stringify(response));
        }

        await notifyAllClients({
          success: true,
          type: 'update_itinerary',
          action: 'single',
          message: 'files.notification.update_itinerary',
          description: 'files.notification.date_success',
          file_id: file_id,
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
      message: 'files.notification.date',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      file_id: file_id,
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