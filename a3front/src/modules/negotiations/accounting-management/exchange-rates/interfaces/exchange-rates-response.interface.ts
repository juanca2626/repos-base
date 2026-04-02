export interface ExchangeRatesResponseInterface {
  id: number;
  user_id: number;
  date_from: string;
  date_to: string;
  currency_id: number;
  exchange_rate: number;
  created_at: string;
}
