import axios from 'axios';
import Cookies from 'js-cookie';
import { removeCookiesCross, setAccessToken } from '@/utils/auth.js';
import type { TokenRefresh } from '@/quotes/interfaces/token-refresh';
import useNotification from '@/quotes/composables/useNotification';

const baseURL = import.meta.env.VITE_APP_TRANSLATION;
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
const translationsApi = axios.create({
  baseURL: baseURL,
});

// Request interceptor for Axios
translationsApi.interceptors.request.use(
  async (config) => {
    config.headers = getAxiosConfigHeaders();
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Function to check if the error is unauthorized
const isUnauthorizedError = (error) => {
  const { response } = error;
  return response && response.status === 401;
};

// Function to renew the access token
const renewToken = async () => {
  const response = await axios.post<TokenRefresh>(`${baseURL}api/refresh`, null, {
    headers: getAxiosConfigHeaders(),
  });

  const { access_token } = response.data;
  return access_token;
};

let refreshingFunc = undefined;
const { showErrorNotification } = useNotification();

// Response interceptor for Axios
translationsApi.interceptors.response.use(
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
  async function (error) {
    const originalConfig = error.config;
    const token = getAccessToken();

    if (!token || !isUnauthorizedError(error)) {
      if (typeof error.response.data === 'string' || error.response.data instanceof String) {
        error.response.data = {
          success: false,
          error: error.data,
        };
      }

      showErrorNotification(error.response.data.error);

      return Promise.reject(error.response);
    }

    try {
      if (!refreshingFunc) {
        refreshingFunc = renewToken();
      }

      const newAccessToken = await refreshingFunc;
      setAccessToken(newAccessToken);

      originalConfig.headers.Authorization = `Bearer ${newAccessToken}`;

      // Retry the original request
      return await axios.request(originalConfig);
    } catch (innerError) {
      if (isUnauthorizedError(innerError)) {
        // If the original request still fails with 401, it means the server returned an invalid token for the refresh request
        removeCookiesCross();
        window.location = `${window.location.origin}/login`;
        throw innerError;
      }
    } finally {
      refreshingFunc = undefined;
    }
  }
);

export default translationsApi;
