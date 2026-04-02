import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SupplierInformationResponse } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

const baseResourceUrl = 'supplier';

async function saveSupplierInformation(supplierId: number, attributes: any): Promise<any> {
  const response = await supplierApi.put(
    `${baseResourceUrl}/supplier-information/${supplierId}`,
    attributes
  );

  return response.data;
}

async function showSupplierInformation(
  supplierId: number
): Promise<ApiResponse<SupplierInformationResponse>> {
  const response = await supplierApi.get(`${baseResourceUrl}/supplier-information/${supplierId}`);

  return response.data;
}

export const useSupplierInformationService = {
  saveSupplierInformation,
  showSupplierInformation,
};
