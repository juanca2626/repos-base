import { notifyAllClients } from '../helpers/websocketNotifier.js';
import { sendRequest, showError, sleep } from '../helpers/request.js';

let file_id, itinerary_id, user_id, user_code;

export const handler = async (event, context) => {
    let response = '';
    let code = 200;

    try
    {
        for (const record of event.Records)
        {
            let body = (typeof record.body === 'string') ? JSON.parse(record.body) : record.body;
            let data = body.payload[0];
            let type = data.type;
            let response_aurora;

            console.log("PAYLOAD: ", JSON.stringify(data));

            user_id = data.userId;
            user_code = data.userCode;
            file_id = data.file_id;
            itinerary_id = data.file_itinerary_id;

            if(type == 'new-flight')
            {
                response_aurora = await sendRequest({
                    method: 'POST',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/flight?flag_lambda=1`,
                    data: data,
                });
            }

            if(type == 'remove-flight')
            {
                response_aurora = await sendRequest({
                    method: 'DELETE',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}?flag_lambda=1`,
                });
            }

            if(type == 'remove-flight-item')
            {
                response_aurora = await sendRequest({
                    method: 'DELETE',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/flight/${data.item_id}?flag_lambda=1`,
                });
            }

            if(type == 'new-flight-item')
            {
                response_aurora = await sendRequest({
                    method: 'POST',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/flight?flag_lambda=1`,
                    data: data,
                });
            }

            if(type == 'update-flight-item')
            {
                response_aurora = await sendRequest({
                    method: 'PUT',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/flight/${data.item_id}?flag_lambda=1`,
                    data: data,
                });
            }

            if(type === 'update-city-iso')
            {
                response_aurora = await sendRequest({
                    method: 'PUT',
                    url: `${process.env.BACKEND_URL_NEW_AURORA}files/${file_id}/itineraries/${itinerary_id}/flight/city-iso?flag_lambda=1`,
                    data: data,
                });
            }

            response = response_aurora.data;

            console.log("RESPUESTA A3: ", JSON.stringify(response));

            if(response.success)
            {
                const update_flight = response.data?.update_flight || false;
                const update_hours = response.data?.update_hours || false;
                const update_accommodations = response.data?.update_accommodations || false;
                const new_itineraries = response.data?.update_itineraries || [];
                const stella = response.data?.stela ?? false;

                if(stella)
                {
                    for(const data_stella of stella)
                    {
                        await sendRequest({
                            method: `${response.data.method}`,
                            url: `${response.data.endpoint}`,
                            data: data_stella,
                        });
                    }
                }

                if(update_flight)
                {
                    for(const data_stella of update_flight.stela)
                    {
                        await sendRequest({
                            method: `${update_flight.method}`,
                            url: `${update_flight.endpoint}`,
                            data: data_stella,
                        });
                    }
                }

                if(update_hours)
                {
                    await sendRequest({
                        method: `${update_hours.method}`,
                        url: `${update_hours.endpoint}`,
                        data: update_hours.stela,
                    });
                }

                if(update_accommodations)
                {
                    await sendRequest({
                        method: `${update_accommodations.method}`,
                        url: `${update_accommodations.endpoint}`,
                        data: update_accommodations.stela,
                    });
                }

                if(type === 'new-flight' || type === 'remove-flight')
                {
                    await notifyAllClients({
                        success: true,
                        type: 'update_file',
                        action: (type === 'new-flight') ? 'new' : 'delete',
                        itinerary_id: itinerary_id,
                        file_id: file_id,
                        message: 'files.notification.update_file',
                        description: (type === 'new-flight') ? 'files.notification.flight_create_success' : 'files.notification.flight_remove_success',
                        user_id: user_id,
                        user_code: user_code,
                    });
                }
                else if(type === 'new-flight-item' || type === 'update-flight-item' || type === 'update-city-iso')
                {
                    await notifyAllClients({
                        success: true,
                        type: 'update_itinerary',
                        action: 'single',
                        itinerary_id: itinerary_id,
                        file_id: file_id,
                        message: 'files.notification.flight',
                        description: (type === 'new-flight-item' || type === 'update-flight-item') ? 'files.notification.flight_item_success' : 'files.notification.flight_city_success',
                        user_id: user_id,
                        user_code: user_code,
                    });
                }
                else
                {
                    await notifyAllClients({
                        success: true,
                        type: 'update_itinerary',
                        action: 'single',
                        file_id: file_id,
                        itinerary_id: itinerary_id,
                        message: 'files.notification.update_itinerary',
                        description: 'files.notification.schedule_success',
                        user_id: user_id,
                        user_code: user_code,
                    });
                }

                await notifyAllClients({
                    success: true,
                    type: 'update_itinerary',
                    action: 'single',
                    itineraries_ids: new_itineraries,
                    file_id: file_id,
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
            file_id: file_id,
            itinerary_id: itinerary_id,
            message: 'files.notification.flight',
            description: response,
            stream_log: context.logStreamName && context.functionName
  ? `${context.logStreamName} (${context.functionName})`
  : '',
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