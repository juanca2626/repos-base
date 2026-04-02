import { sendRequest, showError } from "../helpers/request.js";
import { notifyAllClients } from "../helpers/websocketNotifier.js";

let file_number, file_id, user_id, user_code;

function formatDate(dateStr) {
  if (!dateStr) return '';
  const [year, month, day] = dateStr.split('-');
  return `${day}/${month}/${year}`;
}

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
      const data = body.payload[0];
      const passengers = data.passengers;

      console.log("Received Data:", data);

      file_number = data.file_number;
      file_id = data.file_id ?? null;
      user_id = data.userId ?? '';
      user_code = data.userCode ?? '';

      if(!file_id)
      {
        const response = await sendRequest({
          method: 'GET',
          url: `${process.env.BACKEND_URL_NEW_AURORA}direct/files/basic/${file_number}`,
        });

        file_id = response.data.data.id;
      }
      
      const auroraResponse = await sendRequest({
        method: 'PUT',
        url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/passengers-all?flag_lambda=1`,
        data: passengers,
      });

      const auroraData = auroraResponse.data;
      console.log("Aurora Response:", JSON.stringify(auroraData));

      if (!auroraData.success) {
        throw new Error('Aurora update failed');
      }

      const paxsParams = passengers.map((pax) => {
        const fecnac = formatDate(pax.date_birth);

        return {
          "nroemp": "5",
          "identi": "R",
          "nroref": data.file_number,
          "nrosec": pax.sequence_number,
          "nroord": pax.sequence_number,
          "nropax": pax.localizador ?? '',
          "nombre": `${pax.label}`,
          "tipo": pax.type,
          "sexo": pax.genre,
          "fecnac": fecnac,
          "ciunac": pax.city_iso ?? '',
          "nacion": pax.country_iso ?? '',
          "tipdoc": pax.doctype_iso ?? '',
          "nrodoc": pax.document_number ?? '',
          "tiphab": pax.room_type ?? '',
          "status": "OK",
          "correo": pax.email ?? '',
          "celula": pax.phone,
          "resmed": pax.medical_restrictions,
          "resali": pax.dietary_restrictions,
          "observ": "-",
          "city_ifx_iso": pax.city_iso,
          "city_iso": pax.city_iso,
          "apellidos": pax.surnames,
          "nombres": pax.name,
          "phone_code": pax.phone_code,
          "phone": pax.phone,
          "document_url": pax.document_url ?? ''
        }
      });

      const a2Params = {
        "passengers": paxsParams,
        "repeat": 0,
        "modePassenger": 2,
        "file": data.file_number,
        "type": "file",
        "paxs": passengers.length
      };

      const aurora2Response = await sendRequest({
        method: 'POST',
        url: `${process.env.BACKEND_URL_AURORA}save_passenger`,
        data: a2Params,
      });

      console.log("PARAMS: ", JSON.stringify(a2Params));
      console.log("AURORA 2: ", aurora2Response);

      const stelaParams = auroraData.data.stela;
      console.log("Stela Params:", stelaParams);

      const informixResponse = await sendRequest({
        method: 'POST',
        url: `${process.env.BACKEND_URL_FILES_ONEDB}files/${file_number}/passengers`,
        data: stelaParams,
      });

      response = JSON.stringify(informixResponse.data);

      await notifyAllClients({
        success: true,
        type: 'update_paxs',
        message: 'files.notification.update_paxs',
        description: 'files.notification.passengers_success',
        file_number: file_number,
        user_id: user_id,
        user_code: user_code,
      });
    }
  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_paxs',
      message: 'files.notification.update_paxs',
      description: response,
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      url: error.url,
      data: error.data,
      file_number: file_number,
      file_id: file_id,
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

    console.log("Final Response:", finalResponse);
    return finalResponse;
  }
};