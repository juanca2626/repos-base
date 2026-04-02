export interface SupplierLanguage {
  id: number;
  language: string;
  level: string;
}

export interface SupplierInformationResponse {
  id: number;
  supplier_id: number;
  additional_information: string | null;
  requirements: string | null;
  restrictions: string | null;

  languages?: SupplierLanguage[];
}
