import { API_BASES } from '@/api/constants';
import { fetchData, patchData, postData, download } from '@/api/core/apiClientHelper';
import { paramsToQueryString } from '@/utils/utils';

const baseUrl = API_BASES.ORDER_CONTROL_SERVICE;

export const fetchOrders = async (params: any = {}): Promise<any> => {
  const query = paramsToQueryString(params);
  return fetchData<any>(`/orders?${query}`, {}, baseUrl);
};

export const fetchByCodeAndOrder = async (code: string | number, order: number): Promise<any> => {
  return fetchData<any>(`/orders/${code}/${order}`, {}, baseUrl);
};

export const downloadExcel = async (params: any = {}): Promise<Blob | null> => {
  // Se pasa la URL del endpoint y los parámetros directamente a la función helper `download`.
  const query = paramsToQueryString(params);
  return download(`/orders/download/excel?${query}`, {}, baseUrl);
};

export const updateOrder = async (id: string, payload: any): Promise<any> => {
  return patchData<any>(`/orders/${id}`, payload, baseUrl);
};

export const updateOrderStatus = async (id: string, payload: any): Promise<any> => {
  return patchData<any>(`/orders/${id}/status`, payload, baseUrl);
};

export const updateStatusBulk = async (payload: any): Promise<any> => {
  return postData<any>(`/orders/bulk/status`, payload, baseUrl);
};

export const updateOrderStatusCancellationReason = async (
  id: string,
  payload: any
): Promise<any> => {
  return patchData<any>(`/orders/${id}/status-cancellation-reason`, payload, baseUrl);
};

export const sendFollowUp = async (
  code: string,
  order: number,
  payload: { templateId: string }
): Promise<any> => {
  return postData<any>(`/orders/${code}/${order}/follow-up`, payload, baseUrl);
};

export const sendFollowUpBulk = async (payload: any): Promise<any> => {
  return postData<any>(`/orders/bulk/follow-up`, payload, baseUrl);
};

export const reassignOrder = async (code: string, order: number, payload: any): Promise<any> => {
  return postData<any>(`/orders/${code}/order/${order}/reassign`, payload, baseUrl);
};

export const updateQuotationStatus = async (
  code: number,
  order: number,
  quotation: number,
  payload: { statusId: string }
): Promise<any> => {
  return patchData<any>(
    `/orders/${code}/order/${order}/quotation/${quotation}/status`,
    payload,
    baseUrl
  );
};

export const fetchUsedTemplates = async (): Promise<any> => {
  return fetchData<any>('/orders/filters/used-templates', {}, baseUrl);
};
