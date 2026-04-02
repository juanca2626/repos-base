import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

export const handler = async (event) => {
  let response = '';
  let code = 200;
  let file_id, user_id, user_code;
  
  try
  {
    for (const record of event.Records)
    {
      let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      let data = body.payload[0];

      file_id = data.file_id;
      user_id = data.userId ?? '';
      user_code = data.userCode ?? '';

      let params = {
        "description": data.description,
        "lang": data.lang.toUpperCase(),
        "revision_stages": data.revision_stages,
        "ope_assign_stages": data.ope_assign_stages,
      }
      
      let response_aurora = await sendRequest({
        method: 'PUT',
        url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}?flag_lambda=1`,
        data: params,
      });
      
      response = response_aurora.data;

      if(response.success)
      {
        let is_vip = (response.data.vips.length > 0) ? 1 : 0

        let params_informix = {
          "description": response.data.description,
          "lang": response.data.lang.toUpperCase(),
          "is_vip": is_vip,
          "status": response.data.status.toUpperCase(),
          "revision_stages": data.revision_stages,
          "ope_assign_stages": data.ope_assign_stages,
        }

        let response_informix = await sendRequest({
          method: 'PUT',
          url: `${process.env.BACKEND_URL_FILES_ONEDB}files/${response.data.file_number}`,
          data: params_informix,
        });

        response = JSON.stringify(response_informix.data);
      }

      await notifyAllClients({
        success: true,
        type: 'update_file',
        action: 'status',
        message: 'files.notification.update_file',
        description: 'files.notification.update_success',
        file_id: file_id,
        user_id: user_id,
        user_code: user_code,
      });
    }
  }
  catch(err)
  {
    const error = showError(err);
    response = error.message;
    code = 500;
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