export interface QuotePricePassengersResponse {
  category_id: number;
  quotePricePassengers: QuotePricePassenger[];
}

export interface QuotePricePassenger {
  headers: string[];
  services: ServicesOptionalElement[];
  services_totals: string[];
  services_optionals: ServicesOptionalElement[];
  services_optionals_totals: string[];
}

export interface ServicesOptionalElement {
  date: string;
  services: ServicePassenger[];
}

export interface ServicePassenger {
  type: Type;
  code: string;
  descriptions: string;
  order: number;
  columns: string[];
  room_type?: RoomType;
  meal?: Meal;
}

export enum Meal {
  Breakfast = 'Breakfast',
}

export enum RoomType {
  StandardDbl = 'Standard dbl',
  StandardSgl = 'Standard sgl',
}

export enum Type {
  Hotel = 'hotel',
  Service = 'service',
}
