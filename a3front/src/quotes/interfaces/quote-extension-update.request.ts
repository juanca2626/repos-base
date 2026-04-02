export interface QuoteExtensionUpdateRequest {
  extension_id: number;
  date?: Date | string;
  quote_id: number;
  category_id: number;
}
