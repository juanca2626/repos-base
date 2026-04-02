export interface QuoteServiceUpdateRequest {
  index_service: number;
  quote_service_ids: number[];
  date_in?: Date | string;
  nights?: number;
  quote_id: number;
  move_services?: number;
  days?: number;
  client_id?: string | number;
}
