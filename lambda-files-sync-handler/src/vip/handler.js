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
      let response_aurora = '';
      let is_vip = 0;

      file_id = parseInt(data.file_id);
      user_id = data.userId ?? '';
      user_code = data.userCode ?? '';

      if(data.type == 'save')
      {
        let params = {
          "vip_id": data.vip_id
        }

        response_aurora = await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/vips?flag_lambda=1`,
          method: 'post',
          data: params,
        });

        is_vip = 1;
      }

      if(data.type == 'new')
      {
        let params = {
          "name": data.name,
          "entity": data.entity,
          "file_id": file_id
        }
        
        response_aurora = await sendRequest({
          url: `${process.env.BACKEND_URL_NEW_AURORA}vips?flag_lambda=1`,
          method: 'post',
          data: params,
        });

        is_vip = 1;
      }

      if(data.type == 'delete')
      {
        response_aurora = await sendRequest({
          method: 'delete',
          url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/vips/${data.vip_id}?flag_lambda=1`,
        });

        is_vip = 0;
      }
      
      response = response_aurora.data;
      console.log("RESPONSE AURORA: ", response);

      if(response.success)
      {
        let params_informix = {
          "description": response.data.file.description,
          "lang": response.data.file.lang,
          "is_vip": is_vip,
          "date_in": response.data.file?.date_in ?? '',
          "date_out": response.data.file?.date_out ?? '',
          "status": response.data.file?.status ?? '',
        };
        
        console.log("PARAMS IFX: ", JSON.stringify(params_informix));

        let response_informix = await sendRequest({
          method: 'put',
          url: `${process.env.BACKEND_URL_FILES_ONEDB}files/${response.data.file.file_number}`,
          data: params_informix,
        });
        response = JSON.stringify(response_informix.data);
        console.log("RESPONSE IFX: ", response);
      }

      await notifyAllClients({
        success: true,
        type: 'update_file',
        action: 'vip',
        message: 'files.notification.update_file',
        description: 'files.notification.vip_success',
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

    await notifyAllClients({
      success: false,
      type: 'update_file',
      action: 'vip',
      message: 'files.notification.vip',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      file_id: file_id,
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