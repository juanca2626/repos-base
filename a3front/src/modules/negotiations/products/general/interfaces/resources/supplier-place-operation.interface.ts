import type {
  Country,
  State,
  City,
} from '@/modules/negotiations/products/general/interfaces/resources';

export interface CountryOperation extends Country {
  code: string;
}

export interface StateOperation extends State {
  code: string;
}

export interface CityOperation extends City {
  code: string;
}

export interface SupplierPlaceOperation {
  country: CountryOperation;
  state: StateOperation;
  city: CityOperation | null;
  key: string;
}
