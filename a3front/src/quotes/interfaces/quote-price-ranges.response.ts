export interface QuotePriceRangesResponse {
  category_id: number;
  quotePriceRanges: QuotePriceRange[];
}

export interface QuotePriceRange {
  headers: Header[];
  services: ServicesOptionalElement[];
  services_totals: string[];
  services_optionals: ServicesOptionalElement[];
  services_optionals_totals: string[];
}

export interface Header {
  head: string;
  ranges: Range[];
}

export enum Range {
  DoubleRoom = 'Double Room',
  SingleRoom = 'Single Room',
  TripleRoom = 'Triple Room',
}

export interface ServicesOptionalElement {
  date: string;
  services: ServiceRanger[];
}

export interface ServiceRanger {
  type: Type;
  code: string;
  descriptions: string;
  meal?: Meal;
  columns: number[];
}

export enum Meal {
  Breakfast = 'Breakfast',
}

export enum Type {
  Hotel = 'hotel',
  Service = 'service',
}
