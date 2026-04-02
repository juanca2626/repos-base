import type { QuoteService } from '@/quotes/interfaces/quote.response';

export interface UpdateServicesOrderRequest {
  services: QuoteService[];
  quote_id: number;
}
