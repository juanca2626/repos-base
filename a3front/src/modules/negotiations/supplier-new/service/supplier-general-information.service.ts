import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { GeneralInformationResponse } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

const baseResourceUrl = 'supplier';

async function updateGeneralInformation(supplierId: number, attributes: any): Promise<any> {
  const response = await supplierApi.patch(
    `${baseResourceUrl}/general-information/${supplierId}`,
    attributes
  );

  return response.data;
}

async function showGeneralInformation(
  supplierId: number
): Promise<ApiResponse<GeneralInformationResponse>> {
  const response = await supplierApi.get(`${baseResourceUrl}/general-information/${supplierId}`);

  return response.data;
}

export const useSupplierGeneralInformationService = {
  updateGeneralInformation,
  showGeneralInformation,
};
