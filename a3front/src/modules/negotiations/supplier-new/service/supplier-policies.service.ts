import { supplierApi, directSupportApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type {
  SupplierPolicyCloneResponse,
  SupplierPolicyResponse,
} from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

async function storeSupplierPolicies(attributes: any): Promise<any> {
  const response = await directSupportApi.post('policies', attributes);
  return response.data;
}

async function updateSupplierPolicies(id: string, attributes: any): Promise<any> {
  const response = await supplierApi.put(`supplier-policies/${id}`, attributes);
  return response.data;
}

async function patchSupplierPolicyRules(id: string, attributes: any): Promise<any> {
  const response = await directSupportApi.patch(`policies/${id}/rules`, attributes);
  return response.data;
}

/**
 * Actualiza la información básica de una política (nombre, configuración, segmentación)
 * @param id - ID de la política
 * @param attributes - Datos a actualizar
 * @returns Respuesta del servidor
 */
async function patchSupplierPolicy(id: string, attributes: any): Promise<any> {
  const response = await directSupportApi.patch(`policies/${id}`, attributes);
  return response.data;
}

async function getSupplierPolicies(attributes: any): Promise<any> {
  const response = await supplierApi.get('supplier-policies', { params: attributes });
  return response.data;
}

/**
 * Obtiene las políticas de un proveedor desde el nuevo endpoint
 * @param supplierId - ID del proveedor
 * @param forceRefresh - Si es true, agrega un parámetro de cache-busting para forzar la recarga
 * @returns Lista de políticas del proveedor con reglas
 */
async function getPoliciesBySupplier(
  supplierId: number | string,
  forceRefresh: boolean = false
): Promise<any> {
  const url = `policies/supplier/external/${supplierId}`;
  const config = forceRefresh
    ? {
        params: {
          _t: Date.now(), // Cache-busting parameter
        },
        headers: {
          'Cache-Control': 'no-cache',
          Pragma: 'no-cache',
        },
      }
    : {};
  const response = await directSupportApi.get(url, config);
  return response.data;
}

async function deleteSupplierPolicies(id: number): Promise<any> {
  const response = await supplierApi.delete(`supplier-policy/${id}`);
  return response.data;
}

async function destroySupplierPolicy(id: number): Promise<any> {
  const response = await supplierApi.delete(`supplier-policies/${id}`);
  return response.data;
}

async function updateSupplierPoliciesStatus(id: number, status: boolean): Promise<any> {
  const response = await supplierApi.put(`supplier-policy-status/${id}`, { status });
  return response.data;
}

async function showSupplierPolicies(id: number): Promise<any> {
  const response = await supplierApi.get(`supplier-policy/${id}`);
  return response.data;
}

async function showSupplierPolicy(id: number): Promise<ApiResponse<SupplierPolicyResponse>> {
  const response = await supplierApi.get(`supplier-policies/${id}`);
  return response.data;
}

async function showSupplierPolicyListOptions(supplierId: number | string): Promise<any> {
  const response = await directSupportApi.get(`suppliers/${supplierId}/policies`);
  return response.data;
}

async function showSupplierPolicyCloneData(
  id: number | string
): Promise<ApiResponse<SupplierPolicyCloneResponse>> {
  const response = await supplierApi.get(`supplier-policies/${id}/clone-data`);
  return response.data;
}

/**
 * Obtiene una política específica por ID con todas sus reglas
 * @param id - ID de la política (MongoDB ObjectId)
 * @returns Política completa con reglas
 */
async function getSupplierPolicyById(id: number | string): Promise<any> {
  const response = await directSupportApi.get(`policies/${id}`);
  return response.data;
}

export const useSupplierPoliciesService = {
  storeSupplierPolicies,
  updateSupplierPolicies,
  patchSupplierPolicyRules,
  patchSupplierPolicy,
  showSupplierPolicies,
  getSupplierPolicies,
  getPoliciesBySupplier,
  deleteSupplierPolicies,
  updateSupplierPoliciesStatus,
  showSupplierPolicy,
  destroySupplierPolicy,
  showSupplierPolicyListOptions,
  showSupplierPolicyCloneData,
  getSupplierPolicyById,
};
