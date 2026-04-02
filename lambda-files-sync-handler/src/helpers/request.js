import axios from 'axios';
import CognitoService from './cognitoService.js';

const cognitoService = new CognitoService();

export function sleep(ms = 500) {
  return new Promise(resolve => setTimeout(resolve, ms));
}

export async function getHeaders() {
  const access_token = await cognitoService.getAccessToken();

  return {
    'Authorization': `Bearer ${access_token}`,
    'Content-Type': 'application/json',
  };
}

export async function sendRequest({ url, method = 'get', data = {}, headers = {}, useCognito = true }) {
  let finalHeaders = headers;

  if (useCognito) {
    const cognitoHeaders = await getHeaders();
    finalHeaders = { ...cognitoHeaders, ...headers };
  }

  console.log("Request INFO: ", url, JSON.stringify(data), JSON.stringify(headers));
  
  return axios({ method, url, data, headers: finalHeaders });
}

export function showError(err) {
  console.log("ERROR: ", err);
  let response = '';
  let url = '#';
  let data = '';

  if (err.response) {
    if (err.response?.status === 504) {
      response = `ERROR EN LA COMUNICACIÓN (${err.response.statusText})`;
    } else if (err.response?.status === 401) {
      response = `Por favor, su sesión ha expirado, actualice la pantalla e inténtelo nuevamente.`;
    } else if (err.response) {
      response = `${err.response.data?.message || err.response.data?.data || err.response.data?.error || 'Error de red - Se perdió la conexión con el servidor. Por favor, intentar nuevamente.' }`;
    } else {
      response = 'Error de red - Se perdió la conexión con el servidor. Por favor, intentar nuevamente.';
    }

    url = `${err.response.config.method} ${err.response.config.url}`;
    data = `${err.response.config.data}`;

    if(err.response.config.url.indexOf('/ifx/') > -1)
    {
      response = `${response} (INFORMIX)`;
    }
  } else if (err.request) {
    // La solicitud fue enviada pero no hubo respuesta
    response = `No se recibió respuesta del servidor.`;
  } else {
    // Algo salió mal antes de enviar la solicitud
    response = `Error inesperado: ${err.message}`;
  }

  return {
    message: response,
    url: url,
    data: data,
  };
}