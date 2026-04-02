export interface SupplierTaxAssignmentResponseInterface {
  supplier_sub_classification_id: number;
  supplier_id: number;
  name: string;
  code: string;
  dni: string;
  ruc: string;
  assigned_supplier_tax_id: number;
  sub_classification_supplier_id: number;
  assigned: boolean;
}
