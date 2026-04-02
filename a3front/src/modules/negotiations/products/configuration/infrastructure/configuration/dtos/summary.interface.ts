export interface ProductServiceType {
  id: string;
  originalId: number;
  code: string;
  name: string;
}

export interface SupplierSummary {
  id: string;
  originalId: number;
  code: string;
  name: string;
}

export interface ProductSummary {
  id: string;
  code: string;
  name: string;
  serviceType: ProductServiceType;
}

export interface ProductSupplierSummaryData {
  supplier: SupplierSummary;
  product: ProductSummary;
}
