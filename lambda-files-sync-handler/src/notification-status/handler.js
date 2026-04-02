import { showError, sleep } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

export const handler = async (event, context) => {
  let response = '';
  let code = 200;
  let file_number;

  try {
    for (const record of event.Records) {
      let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
      let data = body.payload[0];
      const { action = '', itinerary_ids = [] } = data;
      file_number = data.file_number;

      console.log("PAYLOAD: ", JSON.stringify(data));

      if(itinerary_ids.length > 0)
      {
        await notifyAllClients({
          success: true,
          action: 'status',
          type: 'update_itinerary',
          message: (action === 'sent-ope') ? 'files.notification.update_itinerary' : null,
          description: (action === 'sent-ope') ? 'files.notification.sent_ope_success' : null,
          file_number: parseInt(file_number),
          itineraries_ids: itinerary_ids,
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