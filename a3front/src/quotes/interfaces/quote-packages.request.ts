export interface PackageRequest {
  lang: string;
  client_id: string;
  type_service: string;
  limit: number;
  date: string;
  quantity_persons: QuantityPersons;
  only_recommended: number;
  rooms: Rooms;
}

export interface QuantityPersons {
  adults: number;
  child_with_bed: number;
  child_without_bed: number;
  age_child: AgeChild[];
}

export interface AgeChild {
  age: number;
}

export interface Rooms {
  quantity_sgl: number;
  quantity_dbl: number;
  quantity_tpl: number;
}
