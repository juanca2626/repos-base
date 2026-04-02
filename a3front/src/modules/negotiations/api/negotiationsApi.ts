import axios, { type AxiosInstance, type AxiosError, type InternalAxiosRequestConfig } from 'axios';
import { API_BASE_URL, getApiUrl } from './config';
// @ts-ignore: util global en proyecto
import { getAccessTokenCognito } from '@/utils/auth';
import { handleErrorMessage } from './responseApi';

type NegotiationRoute = 'SUPPORT' | 'SUPPLIER' | 'TECHNICAL_SHEET' | 'PRODUCT';

/**
 * Crea una instancia Axios con inyección de token y composición de URL opcional por microservicio.
 */
const createAxios = (options: {
  route?: NegotiationRoute;
  baseURL?: string;
  tag?: string;
}): AxiosInstance => {
  const { route, baseURL, tag = route || 'DIRECT' } = options;
  // Solo usar API_BASE_URL si no se pasa baseURL explícito y hay route
  const finalBaseURL = baseURL !== undefined ? baseURL : route ? API_BASE_URL : '';

  const instance = axios.create({
    baseURL: finalBaseURL,
    timeout: 30000,
    headers: {
      'Content-Type': 'application/json;charset=UTF-8',
      Accept: 'application/json',
    },
  });

  const isAbsoluteUrl = (url?: string) => !!url && /^(https?:)?\/\//i.test(url);

  // Request: token + URL del microservicio si procede
  instance.interceptors.request.use(
    (config: InternalAxiosRequestConfig) => {
      const token = getAccessTokenCognito?.();
      if (token) {
        (config.headers as any).Authorization = `Bearer ${token}`;
      }

      if (route && !isAbsoluteUrl(config.url)) {
        config.url = getApiUrl(route, config.url || '');
      }
      return config;
    },
    (error: any) => {
      console.error(`❌ [${tag}] Request config error:`, error);
      return Promise.reject(error);
    }
  );

  // Response: logging básico + manejo 401
  instance.interceptors.response.use(
    (response) => response,
    (error: AxiosError) => {
      const status = error.response?.status;
      const url = (error.config as any)?.url;

      if (status === 401) {
        console.error(`🔒 [${tag}] 401 Unauthorized`, { url });
        handleErrorMessage('No autorizado');
      } else if (!status) {
        console.error(`🌐 [${tag}] Network Error`, error.message);
        handleErrorMessage('Error de red');
      } else if (status === 422) {
        // 422 es un warning de validación manejado por cada llamador con alertas inline
        // No mostrar notificación global de error
        console.warn(`⚠️ [${tag}] 422 Validation Warning`, {
          url,
          message: (error.response?.data as any)?.message,
        });
      } else if (status !== 404) {
        const message = (error.response?.data as any)?.message || error.message;

        console.log('message', message);

        console.error(`❌ [${tag}] Response Error`, {
          status,
          url,
          message: message,
        });

        handleErrorMessage(message);
      }
      return Promise.reject(error);
    }
  );

  return instance;
};

// Instancias para cada microservicio
const supportApi = createAxios({ route: 'SUPPORT' });
const supplierApi = createAxios({ route: 'SUPPLIER' });
const technicalSheetApi = createAxios({ route: 'TECHNICAL_SHEET' });
const productApi = createAxios({ route: 'PRODUCT' });

// API directa para endpoints que no usan la estructura de microservicios
const createDirectApi = (baseURL: string): AxiosInstance => createAxios({ baseURL, tag: 'DIRECT' });

// API para el nuevo endpoint de supports (configurable solo por .env)
const directSupportApiBase = import.meta.env.VITE_APP_SUPPLIER_POLICIES_SERVICE as string;
if (!directSupportApiBase) {
  // console.warn(
  //   '[DirectSupportApi] VITE_DIRECT_SUPPORT_API no está configurado en .env. Variables disponibles:',
  //   import.meta.env
  // );
}
console.log('[DirectSupportApi] Base URL:', directSupportApiBase);
const directSupportApi = createDirectApi(directSupportApiBase || 'http://localhost:3000/api/v1');

export { supportApi, supplierApi, technicalSheetApi, productApi, directSupportApi };
