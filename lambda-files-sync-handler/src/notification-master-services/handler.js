import { showError, sleep } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

export const handler = async (event, context) => {
  let response = '';
  let code = 200;
  let file_number, file_id;

  try {
    for (const record of event.Records) {
      const body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      const data = body.payload[0];
      const { file_itineraries, success, error_message = '' } = data;
      file_number = data.file_number;
      file_id = data.file_id;

      console.log("PAYLOAD: ", JSON.stringify(data));

      if(success)
      {
        await notifyAllClients({
          success: true,
          action: 'master-services',
          type: 'update_itinerary',
          message: 'files.notification.update_itinerary',
          description: 'files.notification.master_services_success',
          file_id: parseInt(file_id),
          file_number: parseInt(file_number),
          itineraries_ids: file_itineraries,
        });
      }
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      action: 'master-services',
      type: 'update_file',
      message: 'files.notification.update_file',
      description: response,
      stream_log: context.logStreamName && context.functionName
        ? `${context.logStreamName} (${context.functionName})`
        : '',
      file_id: parseInt(file_id),
      file_number: parseInt(file_number),
      url: error.url,
      data: error.data,
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