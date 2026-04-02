import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

async function getSupplierAccountingAccount(id: any): Promise<any> {
  const response = await supplierApi.get(`supplier/accounting-account/${id}`);
  return response.data;
}

export const useSupplierAccountContableService = {
  getSupplierAccountingAccount,
};
