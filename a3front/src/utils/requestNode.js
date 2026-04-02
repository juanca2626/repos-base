import axios from 'axios';
import Cookies from 'js-cookie';
import { handleApiError } from '@/utils/handleApiError.js';

const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_COGNITO_LIMATOUR;
const API_NODE = import.meta.env.VITE_APP_BACKEND + '/api/v1/commercial/ifx';
const accessTokenCognito = Cookies.get(TOKEN_KEY);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessTokenCognito}`,
};

const instance = axios.create({
  headers,
  timeout: 35000,
  baseURL: API_NODE,
});

instance.interceptors.response.use(
  (response) => {
    return {
      success: true,
      data: response.data,
      status: response.status,
      code: response.status,
    };
  },
  (error) => handleApiError(error)
);

export default instance;
