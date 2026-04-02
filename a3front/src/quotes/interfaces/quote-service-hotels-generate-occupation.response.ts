export interface QuoteServiceHotelsGenerateOccupationResponse {
  quoteDistributions: QuoteServiceHotelsOccupation[];
}

export interface QuoteServiceHotelsOccupation {
  type_room: string;
  type_room_name: string;
  occupation: number;
  single: number;
  double: number;
  triple: number;
  order: number;
  passengers: QuoteServiceHotelsOccupationPassenger[];
  adult: number;
  child: number;
}

export interface QuoteServiceHotelsOccupationPassenger {
  code: number | string;
  label?: string;
}

export interface QuoteDistributionUpdate {
  distribution_passengers: QuoteServiceHotelsOccupation[];
  quote_id: number;
}
