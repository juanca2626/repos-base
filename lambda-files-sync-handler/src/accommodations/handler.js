import { sendRequest, showError, sleep } from '../helpers/request.js';
import { notifyAllClients } from '../helpers/websocketNotifier.js';

let file_number, itinerary_id, user_id, user_code;

const formatDate = (dateStr) => {
  // Si el formato es YYYY-MM-DD → DD-MM-YYYY
  if (/^\d{4}-\d{2}-\d{2}$/.test(dateStr)) {
    const date = new Date(dateStr);
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
  }

  // Si no es ninguno, retornamos null o puedes lanzar error
  return dateStr;
}

const extractPassengers = (source, type) => {
  const master_services = [];

  console.log("SOURCE: ", source);
  console.log("TYPE: ", type);

  if (type === 'itinerary') {
    const passengers = [];

    source.service.compositions[0].units.forEach(unit => {
      unit.accommodations.forEach(acc => {
        passengers.push(acc.file_passenger_id);
      });
    });

    master_services.push({
      code: source.service.code,
      date_in: formatDate(source.service.date_in),
      start_time: source.service.start_time,
      auto_order: source.service.auto_order,
      sequence_numbers: passengers,
    });
  }

  if (type === 'room') {
    source.room.forEach((new_room) => {
      const passengers = [];

      new_room.units.forEach(unit => {
        unit.accommodations.forEach(acc => {
          passengers.push(acc.file_passenger_id);
        });
      });

      master_services.push({
        code: new_room.itinerary.object_code,
        date_in: formatDate(new_room.itinerary.date_in),
        start_time: new_room.itinerary.start_time,
        auto_order: new_room.auto_order,
        sequence_numbers: passengers,
      });
    });
  }

  if(type === 'unit') {
    const passengers = [];
    const new_room = source.room;

    new_room.units.forEach(unit => {
      unit.accommodations.forEach(acc => {
        passengers.push(acc.file_passenger_id);
      });
    });

    master_services.push({
      code: new_room.itinerary.object_code,
      date_in: formatDate(new_room.itinerary.date_in),
      start_time: new_room.itinerary.start_time,
      auto_order: new_room.auto_order,
      sequence_numbers: passengers,
    });
  }

  return master_services;
};

export const handler = async (event, context) => {
  let response = '';
  let code = 200;

  try {
    for (const record of event.Records) {
      const body = typeof record.body === 'string' ? JSON.parse(record.body) : record.body;
      const data = body.payload[0];
      const { type, object_id, passengers } = data;

      user_id = data.userId ?? '';
      user_code = data.userCode ?? '';
      file_number = data.file_number;
      itinerary_id = data.itinerary_id;

      const auroraBase = process.env.BACKEND_URL_NEW_AURORA;
      const informixBase = process.env.BACKEND_URL_FILES_ONEDB;

      const urlMap = {
        itinerary: `${auroraBase}files/itineraries/${object_id}/passengers?flag_lambda=1`,
        room: `${auroraBase}files/hotel-rooms/${object_id}/passengers?flag_lambda=1`,
        unit: `${auroraBase}files/hotel-rooms/units/${object_id}/passengers?flag_lambda=1`,
      };

      console.log("URL: ", urlMap[type]);

      if(urlMap[type])
      {
        const auroraResponse = await sendRequest({
          method: 'PUT',
          url: urlMap[type],
          data: { passengers },
          headers: {
            responseType: 'json'
          },
        });
  
        response = auroraResponse.data;
  
        if (response.success) {
          console.log("RESPONSE A3: ", JSON.stringify(response.data));
          
          const update_passengers = response.data?.update_passengers ?? response.data;
  
          if(type !== 'itinerary')
          {
            const paramsInformix = extractPassengers(update_passengers, type);
            const informixResponse = await sendRequest({
              method: 'PUT',
              url: `${informixBase}files/${file_number}/services/accommodations`,
              data: {
                master_services: paramsInformix,
              }
            });
  
            console.log("RESPONSE INFORMIX: ", JSON.stringify(informixResponse.data));
          }
          else
          {
            const update_stela = update_passengers ?? false;
  
            if(update_stela && update_stela?.endpoint)
            {
              const paramsInformix = update_stela?.stela;
              const informixResponse = await sendRequest({
                method: update_stela.method,
                url: `${update_stela.endpoint}`,
                data: paramsInformix
              });
    
              console.log("RESPONSE INFORMIX: ", JSON.stringify(informixResponse.data));
            }
          }
  
          await notifyAllClients({
            success: true,
            type: 'update_itinerary',
            action: 'single',
            message: 'files.notification.update_itinerary',
            description: 'files.notification.accommodations_success',
            user_id: user_id,
            user_code: user_code,
            file_number: parseInt(file_number),
            itinerary_id: itinerary_id,
          });
  
          if(type === 'itinerary')
          {
            const update_hours = response.data?.update_hours || false;
            const new_itineraries = response.data?.update_itineraries || [];
  
            if(update_hours && update_hours?.endpoint)
            {
                await sendRequest({
                    method: `${update_hours.method}`,
                    url: `${update_hours.endpoint}`,
                    data: update_hours.stela,
                });
            }
  
            if(new_itineraries.length > 0)
            {
              await notifyAllClients({
                success: true,
                type: 'update_itinerary',
                action: 'single',
                user_id: user_id,
                user_code: user_code,
                itineraries_ids: new_itineraries,
                file_number: parseInt(file_number)
              });
            }
          }
        }
      }
    }

  } catch (err) {
    const error = showError(err);
    response = error.message;
    code = 500;

    await notifyAllClients({
      success: false,
      type: 'update_itinerary',
      action: 'single',
      file_number: file_number,
      itinerary_id: itinerary_id,
      message: 'files.notification.accommodations',
      stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
      description: response,
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

    console.log("Final Response:", finalResponse);
    return finalResponse;
  }
};