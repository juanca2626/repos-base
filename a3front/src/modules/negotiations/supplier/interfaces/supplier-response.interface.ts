export interface Location {
  id: number;
  name: string;
}

export interface OperationResponse {
  supplier_branch_office_id: number;
  sub_classification_supplier_id: number;
  country: Location;
  state: Location;
  city?: Location | null;
  zone?: Location | null;
}

export interface ClassificationResponse {
  supplier_sub_classification_id: number;
  name: string;
  operations: OperationResponse[];
}

export interface SupplierResponse {
  id: number;
  parent_id: number | null;
  code: string;
  name: string;
  business_name: string;
  document: string;
  belongs_company: number;
  service_charges: number;
  commission_charges: number;
  financial_charges: boolean;
  fiscal_address: string;
  commercial_address: string;
  phone: string;
  email: string;
  country: Location;
  state: Location;
  city?: Location;
  zone?: Location;
  observations: string | null;
  authorized_management: number;
  status: boolean;
  classifications: ClassificationResponse[];
}
