import type { LocationOption } from '@/modules/negotiations/supplier/interfaces';

export interface CollaboratorType {
  type: 'treasury' | 'accounting' | 'negotiation';
}

export interface SelectOption {
  label: string;
  value: string | number;
}

export interface SelectMultipleOption {
  value: string;
  name: string;
  label: string;
}

export interface FormStateNegotiation {
  classifications: any | null;
  cityCode: string | null;
  supplierCode: string | null;
  observations: string;
  showSuggestedCodes: boolean;
  authorizedManagement: boolean;
  businessName: string | null;
  name: string | null;
  supplierClassifications: string[];
  rucNumber: string | null;
  belongsCompany: boolean;
  fiscalAddress: string | null;
  applyServicePercentage: boolean;
  serviceCharges: number | null;
  applyCommissionPercentage: boolean;
  commissionCharges: number | null;
  applyFinancialExpenses: boolean;
  phone: string | null;
  email: string | null;
  commercialAddress: string | null;
  comercialLocation: string;
  operationLocations: FormOperationLocation[];
}

export interface FormOperationLocation {
  primaryLocationKey: string | null;
  zoneKey: string | null;
  locationOptionsByZone: LocationOption[];
  supplierBranchOfficeIds: number[];
}

export interface RequestClassification {
  supplier_sub_classification_id: number;
  operations: RequestOperationLocation[];
}

export interface RequestOperationLocation {
  country_id: number;
  state_id: number;
  city_id: number | null;
  zone_id: number | null;
}

export interface RequestSupplierLocationUpdate extends RequestOperationLocation {
  supplier_sub_classification_id: number;
  supplier_branch_office_id: number;
  delete?: boolean;
}

export interface DrawerEmitTypeInterface {
  (event: 'update:showDrawerForm', value: boolean): void;
}

export interface ModalEmitTypeInterface {
  (event: 'update:showModal', value: boolean): void;
}
