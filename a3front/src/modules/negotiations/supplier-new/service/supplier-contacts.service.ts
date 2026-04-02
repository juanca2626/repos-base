import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';

async function createSupplierContacts(attributes: any): Promise<any> {
  const response = await supplierApi.post(`supplier-contact`, attributes);
  return response.data;
}

async function updateSupplierContact(id: number, attributes: any): Promise<any> {
  const response = await supplierApi.put(`supplier-contact/${id}`, attributes);
  return response.data;
}

async function getSupplierContacts(supplierId: any, attributes: any): Promise<any> {
  const response = await supplierApi.get(`supplier-contacts/${supplierId}`, { params: attributes });
  return response.data;
}

async function deleteSupplierContact(id: number): Promise<any> {
  const response = await supplierApi.delete(`supplier-contact/${id}`);
  return response.data;
}

export const useSupplierContactsService = {
  createSupplierContacts,
  updateSupplierContact,
  getSupplierContacts,
  deleteSupplierContact,
};
