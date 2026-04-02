import { showError, sendRequest } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      let data = body.payload[0];
      const { file_number, from = '' } = data;
      const type_ = typeof file_number;
      const files = [];

      console.log(type_);

      if(type_ === 'object' || type_ === 'array')
      {
        files = file_number;
      }
      else
      {
        files.push(file_number);
      }

      for(const file of files)
      {
        const responseAurora = await sendRequest({
          method: "POST",
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/unlock/${file}?flag_lambda=1`,
          data: []
        });
  
        console.log("RESPONSE: ", JSON.stringify(responseAurora.data));
  
        if(from === 'aurora')
        {
          await sendRequest({
            method: responseAurora.data.method,
            url: responseAurora.data.endpoint,
            data: responseAurora.data.stela,
          });
        }
  
        await notifyAllClients({
          success: true,
          action: 'status',
          type: 'update_file',
          message: 'files.notification.update_file',
          description: 'files.notification.status_success',
          file_number: parseInt(file),
        });
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

    console.log("Final Response: ", finalResponse);
    return finalResponse;
  }
};