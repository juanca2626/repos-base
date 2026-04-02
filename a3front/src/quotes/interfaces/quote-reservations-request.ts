export interface QuoteReservastionRequest {
  client_id: string;
  lang: string;
  quote_id: number;
  quote_id_original: number;
  reference: string;
  file_code: string;
  quote_category_id: number;
  services_optionals: any[];
}
