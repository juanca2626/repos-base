import axios, { type AxiosInstance } from 'axios';
import { notification } from 'ant-design-vue';
import { getAccessTokenCognito } from '@/utils/auth';

/**
 * Crea una instancia de Axios configurada con interceptores y cabeceras opcionales.
 * @param baseURL - URL base del cliente Axios.
 * @param headers - Si se deben incluir cabeceras por defecto (Authorization, Content-Type).
 */
const createApiClient = (baseURL: string, headers = true): AxiosInstance => {
  const apiClient = axios.create({ baseURL });

  // Interceptor de cabeceras
  if (headers) {
    apiClient.interceptors.request.use(
      (config) => {
        config.headers['Content-Type'] = 'application/json;charset=UTF-8';

        const token = getAccessTokenCognito();
        if (token) {
          config.headers['Authorization'] = `Bearer ${token}`;
        }

        return config;
      },
      (error) => Promise.reject(error)
    );
  }

  // Interceptor de errores
  apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
      if (error.response) {
        const message = error.response.data?.message;
        notification.error({
          message: 'Error',
          description: Array.isArray(message)
            ? message[0]
            : (message ?? 'Ocurrió un error inesperado.'),
        });
      } else {
        console.error('Network or configuration error:', error.message || error.request);
      }

      return Promise.reject(error);
    }
  );

  return apiClient;
};

export default createApiClient;
