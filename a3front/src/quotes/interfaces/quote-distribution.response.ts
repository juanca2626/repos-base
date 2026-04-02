export interface QuoteDistributionResponse {
  quoteDistributions: QuoteDistribution[];
}

export interface QuoteDistribution {
  type_room: string;
  type_room_name: string;
  occupation: number;
  single: number;
  double: number;
  triple: number;
  order: number;
  passengers: QuoteDistributionPassenger[];
  adult: number;
  child: number;
}

export interface QuoteDistributionPassenger {
  code: number | string;
  label?: any | string;
}

export interface QuoteDistributionUpdate {
  distribution_passengers: QuoteDistribution[];
  quote_id: number;
}
