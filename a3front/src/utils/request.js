import axios from 'axios';
import Cookies from 'js-cookie';
import { handleApiError } from '@/utils/handleApiError.js';
import { isAuthenticated, isAuthenticatedCognito, removeCookiesCross } from '@/utils/auth';

const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_COGNITO_LIMATOUR;
const API = import.meta.env.VITE_APP_BACKEND + '/api/v1/commercial/files-ms';

const accessTokenCognito = Cookies.get(TOKEN_KEY);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessTokenCognito}`,
};

const instance = axios.create({
  headers,
  timeout: 35000,
  baseURL: API,
});

instance.interceptors.response.use(
  (response) => ({
    success: true,
    data: response.data,
    status: response.status,
    code: response.status,
  }),
  async (error) => {
    const originalRequest = error.config;

    // Verifica si es un error 401 no autorizado
    if (
      error.response?.status === 401 &&
      !originalRequest._retry &&
      (!isAuthenticatedCognito() || !isAuthenticated())
    ) {
      originalRequest._retry = true;

      // Limpiar auth
      removeCookiesCross(); // Borra cookies, localStorage, etc.
    }

    return handleApiError(error);
  }
);

export default instance;
