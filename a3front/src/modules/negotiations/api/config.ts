export const API_ROUTES = {
  SUPPORT: 'support-ms',
  SUPPLIER: 'supplier-ms',
  TECHNICAL_SHEET: 'technical-sheets-ms',
  PRODUCT: 'product-ms',
};

export const API_BASE_URL =
  typeof window !== 'undefined' && window.API_GATEWAY_BACKEND
    ? window.API_GATEWAY_BACKEND
    : import.meta.env.VITE_APP_BACKEND;

export function getApiUrl(route: keyof typeof API_ROUTES, path: string = ''): string {
  return `${API_BASE_URL}/api/v1/neg/${API_ROUTES[route]}/${path}`.replace(/\/+$/, '');
}
