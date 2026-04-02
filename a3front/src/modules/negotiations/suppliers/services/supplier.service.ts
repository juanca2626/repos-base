import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

const baseResourceUrl = 'supplier';

async function inactivateSupplier(id: number): Promise<any> {
  const response = await supplierApi.patch(`${baseResourceUrl}/inactivate/${id}`);
  return response.data;
}

export const useSupplierService = {
  inactivateSupplier,
};
