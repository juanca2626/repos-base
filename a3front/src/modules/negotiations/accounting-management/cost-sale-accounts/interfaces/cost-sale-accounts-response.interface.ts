interface ServiceClassification {
  id: number;
  name: string;
}
export interface CostSaleAccountsResponseInterface {
  id: number;
  user_id: number;
  service_classification_id: number;
  date_from: string;
  date_to: string;
  cost_account: string;
  sale_account: string;
  created_at: string;
  service_classification: ServiceClassification;
}
