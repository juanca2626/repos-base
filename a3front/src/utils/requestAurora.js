import axios from 'axios';
import Cookies from 'js-cookie';
import { handleApiError } from '@/utils/handleApiError.js';

const TOKEN_KEY = import.meta.env.VITE_TOKEN_KEY_LIMATOUR;
const AURORA_BACKEND_URL = import.meta.env.VITE_APP_BACKEND_URL;
const USER_ID = import.meta.env.VITE_USER_KEY_LIMATOUR;

const accessToken = Cookies.get(TOKEN_KEY);
const userId = Cookies.get(USER_ID);

const headers = {
  'Content-Type': 'application/json',
  Authorization: `Bearer ${accessToken}`,
  'User-Id': `${userId}`,
};

const instance = axios.create({
  headers,
  // timeout: 35000,
  baseURL: AURORA_BACKEND_URL,
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
