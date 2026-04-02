import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_id, user_id, user_code;

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
      const data = body.payload[0];

      user_id = data.userId;
      user_code = data.userCode;
      file_id = data.file_id;

      const activationUrl = `${process.env.BACKEND_URL_NEW_AURORA}files/${data.file_id}/activate?flag_lambda=1`;

      const activationResult = await sendRequest({
        method: 'PUT',
        url: activationUrl,
        data: { status_reason_id: data.status_reason_id },
      });

      if (activationResult.success) {
        const reservationUrl = `${process.env.BACKEND_URL_AURORA.replace('/api', '')}services/hotels/reservation/add`;

        const reservationResult = await sendRequest({
          method: 'POST',
          url: reservationUrl,
          data,
          headers: {
            'Authorization': `Bearer ${data.access_token}`,
            'Content-Type': 'application/json',
          },
          useCognito: false,
        });

        response = JSON.stringify(reservationResult);

        await notifyAllClients({
          success: true,
          type: 'update_file',
          message: 'files.notification.update_file',
          description: 'files.notification.update_file_success',
          file_id: file_id,
        });
      } else {
        response = JSON.stringify(activationResult);

        await notifyAllClients({
          success: false,
          type: 'update_file',
          file_id: file_id,
          message: 'files.notification.update_file',
          description: response,
        });
      }
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_file',
      file_id: file_id,
      message: 'Modificación de File',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
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

    console.log('Final Response:', finalResponse);
    return finalResponse;
  }
};