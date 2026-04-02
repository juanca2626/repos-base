export interface FormExchangeRatesInterface {
  id?: number | null;
  date: [string | null, string | null];
  currency_id: number | null;
  exchange_rate: number;
  method: string;
}
