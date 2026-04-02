import createApiClient from '@/api/core/axiosConfig';
import type { ApiResponse } from '@/api/interfaces';
import { API_BASES } from '@/api/constants';

// Base URL por defecto
const defaultBaseURL = API_BASES.BACKEND_A3;

/**
 * Crea un cliente API con un `baseURL` dinámico.
 * @param baseURL - La URL base de la API (opcional).
 * @returns Instancia del cliente API.
 */
const getApiClient = (baseURL?: string, headers?: boolean) => {
  return createApiClient(baseURL || defaultBaseURL, headers ?? true);
};

/**
 * Realiza una solicitud GET para obtener datos de la API.
 * @param url - La URL del endpoint de la API.
 * @param params - Parámetros opcionales para la solicitud.
 * @param customBaseURL - Base URL personalizada (opcional).
 * @returns Respuesta de la API o null en caso de error.
 */
export const fetchData = async <T>(
  url: string,
  params: any = {},
  customBaseURL?: string
): Promise<ApiResponse<T> | null> => {
  console.log('🚀 ~ fetchData ~ url:', url);
  try {
    const apiClient = getApiClient(customBaseURL);
    const response = await apiClient.get<ApiResponse<T>>(url, { params });
    return response.data;
  } catch (error) {
    console.error(`Error fetching data from ${url}:`, error);
    return null;
  }
};

/**
 * Realiza una solicitud POST para enviar datos a la API.
 * @param url - La URL del endpoint de la API.
 * @param data - El cuerpo de la solicitud que se enviará.
 * @param customBaseURL - Base URL personalizada (opcional).
 * @returns Respuesta de la API o null en caso de error.
 */
export const postData = async <T>(
  url: string,
  data: any,
  customBaseURL?: string,
  headers?: boolean
): Promise<ApiResponse<T> | null> => {
  try {
    const apiClient = getApiClient(customBaseURL, headers);
    const response = await apiClient.post<ApiResponse<T>>(url, data);
    console.log(response.data);
    return response.data;
  } catch (error) {
    console.error(`Error posting data to ${url}:`, error);
    return null;
  }
};

/**
 * Realiza una solicitud PATCH para actualizar datos en la API.
 * @param url - La URL del endpoint de la API.
 * @param data - El cuerpo de la solicitud que se enviará.
 * @param customBaseURL - Base URL personalizada (opcional).
 * @returns Respuesta de la API o null en caso de error.
 */
export const patchData = async <T>(
  url: string,
  data: any,
  customBaseURL?: string
): Promise<ApiResponse<T> | null> => {
  try {
    const apiClient = getApiClient(customBaseURL);
    const response = await apiClient.patch<ApiResponse<T>>(url, data);
    return response.data;
  } catch (error) {
    console.error(`Error patching data to ${url}:`, error);
    return null;
  }
};

/**
 * Realiza una solicitud DELETE para eliminar datos en la API.
 * @param url - La URL del endpoint de la API.
 * @param params - Parámetros opcionales para la solicitud.
 * @param customBaseURL - Base URL personalizada (opcional).
 * @returns Respuesta de la API o null en caso de error.
 */
export const removeData = async <T>(
  url: string,
  data?: any,
  customBaseURL?: string
): Promise<ApiResponse<T> | null> => {
  try {
    const apiClient = getApiClient(customBaseURL);
    const response = await apiClient.delete<ApiResponse<T>>(url, { data });
    return response.data;
  } catch (error) {
    console.error(`Error deleting data from ${url}:`, error);
    return null;
  }
};

/**
 * Realiza una solicitud GET para descargar un archivo (ej. Excel).
 * @param url - La URL del endpoint de la API.
 * @param params - Parámetros opcionales para la solicitud.
 * @param customBaseURL - Base URL personalizada (opcional).
 * @returns Un Blob con el contenido del archivo o null en caso de error.
 */
export const download = async (
  url: string,
  params: any = {},
  customBaseURL?: string
): Promise<Blob | null> => {
  try {
    const apiClient = getApiClient(customBaseURL);
    const response = await apiClient.get(url, {
      params,
      responseType: 'blob', // Clave para manejar la respuesta como un archivo binario
    });
    return response.data;
  } catch (error) {
    console.error(`Error downloading file from ${url}:`, error);
    return null;
  }
};
