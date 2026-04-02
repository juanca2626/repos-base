export interface QuoteServiceAddRequest {
  adult: number;
  categories: number[];
  child: number;
  date_in: Date | string;
  date_out: Date | string;
  double: number;
  extension_parent_id: string | null;
  object_id: number;
  on_request?: number;
  quote_id: number;
  service_code: string;
  service_rate_ids: number[];
  single: number;
  triple: number;
  type: string;
}
