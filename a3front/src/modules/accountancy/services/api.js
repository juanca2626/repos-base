import axios from 'axios';
import { getAccessTokenCognito } from '@/utils/auth';

const createAccountancyApi = (route) => {
  // Función para obtener el token
  const bearerToken = () => {
    const token = getAccessTokenCognito();
    return token ? `Bearer ${token}` : '';
  };

  // Función para obtener la URL base del microservicio
  const getBaseUrl = () => {
    // Determinar si es una ruta pública
    const isPublicRoute = route.includes('_PUBLIC');

    if (isPublicRoute) {
      const baseUrl = window.APINODE_ACCOUNTANCY_MS_PUBLIC || 'http://localhost:3005';
      return baseUrl.replace(/\/$/, '');
    } else {
      const isBalancerRoute = route.includes('_BALANCER');

      if (isBalancerRoute) {
        const baseUrl = window.APINODE_ACCOUNTANCY_MS_BALANCER || 'http://localhost:3005';
        return baseUrl.replace(/\/$/, '');
      } else {
        const baseUrl = window.APINODE_ACCOUNTANCY_MS || 'http://localhost:3005';
        return baseUrl.replace(/\/$/, '');
      }
    }
  };

  // Mapeo de rutas a endpoints específicos
  const getRoutePath = (route) => {
    const routes = {
      NON_CONFORMING_PRODUCTS: '/non-conforming-products',
      NON_CONFORMING_PRODUCTS_BALANCER: '/non-conforming-products',
      NON_CONFORMING_PRODUCTS_PUBLIC: '/public/non-conforming-products',
      CONGRATULATIONS: '/congratulations',
      MANAGEMENT_MONITORING: '/management-monitoring',
      SUGGESTIONS_FOR_IMPROVEMENT: '/suggestions-for-improvement',
      MAINTENANCE_SANCTIONS: '/maintenance-sanctions',
      USERS: '/users',
      EXPORTS: '/exports',
      EXPORTS_BALANCER: '/exports',
      CLAIMS: '/claims',
      REPORTS: '/reports',
    };
    return routes[route];
  };

  const instance = axios.create({
    baseURL: getBaseUrl(),
    headers: {
      'Content-Type': 'application/json;charset=UTF-8',
    },
    timeout: 30000,
  });

  // Interceptor de request
  instance.interceptors.request.use(
    (config) => {
      const token = bearerToken();
      const isPublicRoute = route.includes('_PUBLIC');

      // Solo agregar token si NO es una ruta pública
      if (!isPublicRoute) {
        if (!token) {
          console.warn('No se encontró token de autenticación');
        }

        if (token) {
          config.headers.Authorization = token;
        }
      } else {
        console.log('Usando ruta pública - sin token requerido');
      }

      if (config.url) {
        const routePath = getRoutePath(route);
        config.url = `${routePath}${config.url.startsWith('/') ? '' : '/'}${config.url}`;
      }

      return config;
    },
    (error) => {
      console.error(`[API ${route}] Error al configurar la solicitud:`, error);
      return Promise.reject(error);
    }
  );

  // Interceptor de response
  instance.interceptors.response.use(
    (response) => response,
    (error) => {
      // Solo redirigir si NO es una ruta pública
      const isPublicRoute = route.includes('_PUBLIC');

      if (!isPublicRoute && error.response?.status === 401) {
        console.error('Error de autenticación - Token inválido o expirado');
        localStorage.removeItem('cognito_token');
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
  );

  return instance;
};

// Creamos instancias para cada módulo
const exportsApi = createAccountancyApi('EXPORTS');
const exportsApi_BALANCER = createAccountancyApi('EXPORTS_BALANCER');
const nonConformingProductsApi = createAccountancyApi('NON_CONFORMING_PRODUCTS');
const nonConformingProductsApi_PUBLIC = createAccountancyApi('NON_CONFORMING_PRODUCTS_PUBLIC');
const nonConformingProductsApi_BALANCER = createAccountancyApi('NON_CONFORMING_PRODUCTS_BALANCER');
const congratulationsApi = createAccountancyApi('CONGRATULATIONS');
const managementMonitoringApi = createAccountancyApi('MANAGEMENT_MONITORING');
const suggestionsForImprovementApi = createAccountancyApi('SUGGESTIONS_FOR_IMPROVEMENT');
const maintenanceSanctionsApi = createAccountancyApi('MAINTENANCE_SANCTIONS');
const usersApi = createAccountancyApi('USERS');
const claimsApi = createAccountancyApi('CLAIMS');
const reportsApi = createAccountancyApi('REPORTS');

export {
  exportsApi,
  exportsApi_BALANCER,
  nonConformingProductsApi,
  nonConformingProductsApi_PUBLIC,
  nonConformingProductsApi_BALANCER,
  congratulationsApi,
  managementMonitoringApi,
  suggestionsForImprovementApi,
  maintenanceSanctionsApi,
  usersApi,
  claimsApi,
  reportsApi,
};
