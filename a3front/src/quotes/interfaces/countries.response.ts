export interface CountriesResponse {
  success: boolean;
  data: Country[];
}

export interface Country {
  id: number;
  iso: string;
  iso_ifx: string;
  local_tax: number;
  foreign_tax: number;
  local_service: number;
  foreign_service: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
  translations: Translation[];
}

export interface Translation {
  id: number;
  type: Type;
  object_id: number;
  slug: Slug;
  value: string;
  language_id: number;
  created_at: Date;
  updated_at: Date;
  deleted_at: null;
}

export enum Slug {
  CountryName = 'country_name',
}

export enum Type {
  Country = 'country',
}
