export interface FormStateTreasury {
  creditDays: number | null;
  creditDaysSunat: number | null;
  startDateSunat: string | null;
}

export interface SupplierPaymentTermResponse {
  id: number;
  supplier_id: number;
  credit_days: number;
  credit_days_sunat: number;
  sunat_start_date: string;
}
