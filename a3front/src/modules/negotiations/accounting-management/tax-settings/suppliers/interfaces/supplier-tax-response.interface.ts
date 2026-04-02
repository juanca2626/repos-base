interface Law {
  id: number;
  name: string;
  date_from: string;
  date_to: string;
}

interface TaxSupplierClassification {
  id: number;
  supplier_sub_classification_id: number;
  name: string;
}

export interface SupplierTaxResponseInterface {
  id: number;
  law: Law;
  taxes_supplier_classifications: TaxSupplierClassification[];
  status_assignment: number;
  status: number;
  creation_date: string;
}
