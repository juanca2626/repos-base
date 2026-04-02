import { supplierApi } from '@/modules/negotiations/api/negotiationsApi';
import type { ApiResponse } from '@/modules/negotiations/interfaces/api-response.interface';
import type { SupplierPolicyRuleResponse } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

const baseResourceUrl = 'supplier-policy-rules';

async function fetchResources(): Promise<any> {
  const response = await supplierApi.get(`${baseResourceUrl}/resources`, {
    params: {
      keys: [
        'condition_types',
        'payment_types',
        'value_types',
        'time_units',
        'confirmation_types',
        'release_types',
      ],
    },
  });
  return response.data;
}

async function storeSupplierPolicyRules(attributes: any): Promise<any> {
  const response = await supplierApi.post(`${baseResourceUrl}`, attributes);
  return response.data;
}

async function updateSupplierPolicyRules(attributes: any): Promise<any> {
  const response = await supplierApi.put(`${baseResourceUrl}`, attributes);
  return response.data;
}

async function showSupplierPolicyRules(
  supplierPolicyId: string
): Promise<ApiResponse<SupplierPolicyRuleResponse>> {
  const response = await supplierApi.get(`${baseResourceUrl}/${supplierPolicyId}`);
  return response.data;
}

export const usePolicyRulesService = {
  fetchResources,
  storeSupplierPolicyRules,
  showSupplierPolicyRules,
  updateSupplierPolicyRules,
};
