export interface FormStateAccounting {
  ivaOptionsId: number | null;
  taxRatesId: number | null;
  lastBillingDate: string | null;
}

export interface SupplierTaxConditionResponse {
  id: number;
  supplier_id: number;
  tax_rate: {
    id: number;
    name: string;
  };
  iva_option: {
    id: number;
    name: string;
  };
  last_billing_date: string;
}
