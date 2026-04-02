export interface QuoteHotelsSearchRequest {
  allWords?: boolean;
  date_from: Date | string;
  date_to: Date | string;
  destiny: Destiny;
  hotels_id?: string[] | number[];
  hotels_search_code?: string;
  lang?: string;
  quantity_persons_rooms: QuantityPersonsRooms[];
  quantity_rooms: number;
  set_markup: number;
  type_classes?: number[] | string[];
  typeclass_id: number | string;
  zero_rates: boolean;
  promotional_rate?: boolean | number;
  price_range?: { min: number; max: number };
  rate_plan_room_search?: number[];
}

export interface Destiny {
  code: string;
  label: string;
}

export interface QuantityPersonsRooms {
  adults: number;
  child: number;
  ages_child: AgesChild[];
}

export interface AgesChild {
  child: number;
  age: number;
}
