import { supplierApi, directSupportApi } from '@/modules/negotiations/api/negotiationsApi';

async function createSupplier(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post('supplier', attributes);
  return response.data;
}

async function updateSupplier(id: number, attributes: any = {}): Promise<any> {
  const response = await supplierApi.put(`supplier/${id}`, attributes);
  return response.data;
}

async function showSupplier(id: number | undefined): Promise<any> {
  const response = await supplierApi.get(`supplier/${id}`);
  return response.data;
}

async function showModules(attributes: any = {}): Promise<any> {
  const response = await supplierApi.get(`supplier/modules`, { params: attributes });
  return response.data;
}

async function updateOrCreateSupplierPaymentTerm(
  attributes: any = {},
  supplierId: number | undefined
): Promise<any> {
  const response = await supplierApi.post(`supplierPaymentTerm/${supplierId}`, attributes);
  return response.data;
}

async function showSupplierPaymentTerm(supplierId: number | undefined): Promise<any> {
  const response = await supplierApi.get(`supplierPaymentTerm/${supplierId}`);
  return response.data;
}

async function updateOrCreateSupplierTaxCondition(
  attributes: any = {},
  supplierId: number | undefined
): Promise<any> {
  const response = await supplierApi.post(`supplierTaxCondition/${supplierId}`, attributes);
  return response.data;
}

async function showSupplierTaxCondition(supplierId: number | undefined): Promise<any> {
  const response = await supplierApi.get(`supplierTaxCondition/${supplierId}`);
  return response.data;
}

async function createGenerateCodeAutomatic(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post(`supplier/generateCode`, attributes);
  return response.data;
}

async function createSupplierTributaryInformation(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post('supplier/tributary-information', attributes);
  return response.data;
}

async function updateSupplierTributaryInformation(id: number, attributes: any = {}): Promise<any> {
  const response = await supplierApi.put(`supplier/tributary-information/${id}`, attributes);
  return response.data;
}

async function createSupplierBankInformation(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post('supplier/bank-information', attributes);
  return response.data;
}

async function updateSupplierBankInformation(id: number, attributes: any = {}): Promise<any> {
  const response = await supplierApi.put(`supplier/bank-information/${id}`, attributes);
  return response.data;
}

async function showSupplierBankInformationModule(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post(`supplier/bank-information-show`, attributes);
  return response.data;
}

async function showSupplierBankInformation(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post(`supplier/tributary-information-show`, attributes);
  return response.data;
}

async function updateOrCreateSupplierClassification(attributes: any = {}): Promise<any> {
  const response = await supplierApi.post(`supplier/classifications`, attributes);
  return response.data;
}

async function updateOrCreateSupplierLocations(
  supplierId: number | undefined,
  attributes: any = {}
): Promise<any> {
  const response = await supplierApi.post(`supplier/locations/${supplierId}`, attributes);
  return response.data;
}

async function updateOrCreateSupplierInformationCommercial(
  supplierId: number | undefined,
  attributes: any = {}
): Promise<any> {
  const response = await supplierApi.post(
    `supplier/information-commercial/${supplierId}`,
    attributes
  );
  return response.data;
}

// Para edición (con supplier_id)
async function showSupplierCompleteData(supplierId: number, params?: any): Promise<any> {
  const response = await supplierApi.get(`supplier/complete-data/${supplierId}`, { params });
  return response.data;
}

// Para registro (sin supplier_id)
async function showSupplierCompleteDataForRegistration(params?: any): Promise<any> {
  const response = await supplierApi.get('supplier/complete-data', { params });
  return response.data;
}

async function showLanguages(): Promise<any> {
  const response = await supplierApi.get('supplier/information-commercial/languages');
  return response.data;
}

async function showSupplierLanguages(supplierId: number): Promise<any> {
  const response = await supplierApi.get(`supplier/information-commercial/languages/${supplierId}`);
  return response.data;
}

async function updateOrCreateSupplierLanguage(
  supplierId: number,
  attributes: any = {}
): Promise<any> {
  const response = await supplierApi.post(
    `supplier/information-commercial/languages/${supplierId}`,
    attributes
  );
  return response.data;
}

async function showSupplierCompletePolicies(supplierId: string, params?: any): Promise<any> {
  const response = await supplierApi.get(`supplier/complete-policies/${supplierId}`, { params });
  return response.data;
}

async function showSupplierPoliciesClientMarkets(): Promise<any> {
  const response = await supplierApi.get('supplier/policies/client-markets');
  return response.data;
}

async function updateOrCreateSupplierAttractions(
  supplierId: number | undefined,
  attributes: any = {}
): Promise<any> {
  const response = await supplierApi.post(
    `supplier/information-commercial/attractions/${supplierId}`,
    attributes
  );
  return response.data;
}

async function showSupplierAttractions(supplierId: number | undefined): Promise<any> {
  const response = await supplierApi.get(
    `supplier/information-commercial/attractions/${supplierId}`
  );
  return response.data;
}

async function showSupportsByCode(code: string = 'default'): Promise<any> {
  const response = await directSupportApi.get(`supports/by-code/${code}`);
  return response.data;
}

async function showMarketsPolicies(): Promise<any> {
  const response = await directSupportApi.get('markets');
  return response.data;
}

async function showClientsPolicies(): Promise<any> {
  const response = await directSupportApi.get('clients');
  return response.data;
}

async function showServiceTypesPolicies(): Promise<any> {
  const response = await directSupportApi.get('service-types');
  return response.data;
}

async function showSeasonsPolicies(): Promise<any> {
  const response = await directSupportApi.get('seasons');
  return response.data;
}

export const useSupplierService = {
  createSupplier,
  updateSupplier,
  showSupplier,
  showModules,
  updateOrCreateSupplierPaymentTerm,
  showSupplierPaymentTerm,
  updateOrCreateSupplierTaxCondition,
  showSupplierTaxCondition,
  createGenerateCodeAutomatic,
  createSupplierTributaryInformation,
  updateSupplierTributaryInformation,
  createSupplierBankInformation,
  updateSupplierBankInformation,
  showSupplierBankInformation,
  showSupplierBankInformationModule,
  updateOrCreateSupplierClassification,
  updateOrCreateSupplierLocations,
  updateOrCreateSupplierInformationCommercial,
  showSupplierCompleteData,
  showSupplierCompleteDataForRegistration,
  showLanguages,
  showSupplierLanguages,
  updateOrCreateSupplierLanguage,
  showSupplierCompletePolicies,
  showSupplierPoliciesClientMarkets,
  updateOrCreateSupplierAttractions,
  showSupplierAttractions,
  showSupportsByCode,
  showMarketsPolicies,
  showClientsPolicies,
  showServiceTypesPolicies,
  showSeasonsPolicies,
};
