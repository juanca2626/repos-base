import { sendRequest, showError } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

export const handler = async (event) => {
  let statusCode = 200;
  let body = '';

  try {
    for (const record of event.Records) {
      const recordBody = typeof record.body === 'string'
        ? JSON.parse(record.body)
        : record.body;

      const payload = recordBody?.payload?.[0];
      if (!payload) throw new Error('Payload is missing');

      const { action = 'update', type = 'statement', file_id, userId = '', userCode = '', params = false } = payload;

      if (!file_id || !type || !userId) {
        throw new Error('Missing required data: file_id, type, userId');
      }

      if(params)
      {
        // Preparar parámetros
        params.user_id = userId;
        params.details = params.details?.map((detail) => ({
          ...detail,
          amount: parseFloat(detail.unit_price * detail.quantity).toFixed(2),
        })) || [];
      }

      // Determinar método y endpoint
      let method, endpoint;
      switch (type) {
        case 'statement':
          method = (action === 'update') ? 'PUT' : 'POST';
          endpoint = `files/${file_id}/statement`;
          break;
        case 'debit_note':
          method = 'POST';
          endpoint = `files/${file_id}/debit-note`;
          break;
        case 'credit_note':
          method = 'POST';
          endpoint = `files/${file_id}/credit-note`;
          break;
        default:
          throw new Error(`Unknown type: ${type}`);
      }

      // Enviar a Aurora
      const auroraResponse = await sendRequest({
        method,
        url: `${process.env.BACKEND_URL_NEW_AURORA}${endpoint}?flag_lambda=1`,
        data: params ?? [],
      });

      const auroraData = auroraResponse.data;

      if (auroraData.success) {
        // Enviar a Informix
        const informixResponse = await sendRequest({
          method: auroraData.data.method,
          url: auroraData.data.endpoint,
          data: auroraData.data.stela,
        });

        body = JSON.stringify(informixResponse.data);

        await notifyAllClients({
          success: true,
          type: 'update_statement',
          user_id: userId,
          user_code: userCode,
          message: 'files.notification.update_file',
          description: 'files.notification.statement_success',
          file_id: parseInt(file_id),
        });

      } else {
        throw new Error(auroraData.message || 'Aurora response unsuccessful');
      }
    }
  } catch (err) {
    const error = showError(err);
    body = error.message;
    statusCode = 500;
  } finally {
    const finalResponse = {
      statusCode,
      body,
    };
    console.log('Final Response:', finalResponse);
    return finalResponse;
  }
};