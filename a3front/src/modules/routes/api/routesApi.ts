import axios from 'axios';
/*const baseURL = 'http://127.0.0.1:3001';*/
const baseURL = import.meta.env.VITE_APP_BACKEND_URL;

export const routesApi = axios.create({
  baseURL,
  headers: {
    'Content-Type': 'application/json;charset=UTF-8',
  },
});
