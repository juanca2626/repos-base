export interface PolicyCloneSearchForm {
  supplierId: string | null; // MongoDB ObjectId
  supplierPolicyId: string | null; // MongoDB ObjectId
}

export interface PolicyCloneSearchModalEmit {
  (event: 'update:showModal', value: boolean): void;
}
