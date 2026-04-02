import axios, { type AxiosError, type InternalAxiosRequestConfig } from 'axios';
import Cookies from 'js-cookie';
import { removeCookiesCross, setAccessToken } from '@/utils/auth';
import type { TokenRefresh } from '@/quotes/interfaces/token-refresh';
import useNotification from '@/quotes/composables/useNotification';

const baseURL = window.url_back_a2;
const tokenKey = import.meta.env.VITE_TOKEN_KEY_LIMATOUR;

// Function to retrieve the access token
const getAccessToken = () => {
  return Cookies.get(tokenKey);
};

// Function to obtain common Axios headers
const getAxiosConfigHeaders = () => {
  const accessToken = getAccessToken();

  return {
    Authorization: `Bearer ${accessToken}`,
    Accept: 'application/json',
    'Content-Type': 'application/json;charset=UTF-8',
  };
};

// Axios configuration for API requests
const hotelsApi = axios.create({
  baseURL: baseURL,
});

// Request interceptor for Axios
hotelsApi.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    const headers = getAxiosConfigHeaders();
    if (config.headers) {
      Object.assign(config.headers, headers);
    }
    return config;
  },
  (error: AxiosError) => {
    return Promise.reject(error);
  }
);

// Function to check if the error is unauthorized
const isUnauthorizedError = (error: any): boolean => {
  const { response } = error;
  return response && response.status === 401;
};

// Function to renew the access token
const renewToken = async (): Promise<string> => {
  const response = await axios.post<TokenRefresh>(`${baseURL}api/refresh`, null, {
    headers: getAxiosConfigHeaders(),
  });

  const { access_token } = response.data;
  return access_token;
};

let refreshingFunc: Promise<string> | undefined = undefined;
const { showErrorNotification } = useNotification();

// Response interceptor for Axios
hotelsApi.interceptors.response.use(
  (response) => {
    if (
      typeof response.data === 'string' ||
      response.data instanceof String ||
      Array.isArray(response.data)
    ) {
      response.data = {
        success: true,
        data: response.data,
      };
    }

    if (
      typeof response.data === 'string' ||
      response.data instanceof String ||
      Array.isArray(response.data)
    ) {
      response.data = {
        success: true,
        data: response.data,
      };
    }

    if (!response.data.hasOwnProperty('success')) {
      response.data.success = true;
    }

    if (!response.data.success) {
      showErrorNotification(response.data.error);
    }

    return response;
  },
  async function (error: any) {
    const originalConfig = error.config as InternalAxiosRequestConfig;
    const token = getAccessToken();

    if (!token || !isUnauthorizedError(error)) {
      if (
        error.response &&
        (typeof error.response.data === 'string' || error.response.data instanceof String)
      ) {
        error.response.data = {
          success: false,
          error: error.data,
        };
      }

      if (error.response?.data?.error) {
        showErrorNotification(error.response.data.error);
      }

      return Promise.reject(error.response);
    }

    try {
      if (!refreshingFunc) {
        refreshingFunc = renewToken();
      }

      const newAccessToken = await refreshingFunc;
      setAccessToken(newAccessToken);

      if (originalConfig.headers) {
        originalConfig.headers.Authorization = `Bearer ${newAccessToken}`;
      }

      // Retry the original request
      return await axios.request(originalConfig);
    } catch (innerError: any) {
      if (isUnauthorizedError(innerError)) {
        // If the original request still fails with 401, it means the server returned an invalid token for the refresh request
        removeCookiesCross();
        window.location.href = `${window.location.origin}/login`;
        throw innerError;
      }
    } finally {
      refreshingFunc = undefined;
    }
  }
);

export default hotelsApi;
