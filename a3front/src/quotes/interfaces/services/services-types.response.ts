export interface ServicesTypesResponse {
  success: boolean;
  data: ServicesType[];
}

export interface ServicesType {
  id: number;
  code: string;
  label: string;
  abbreviation: string;
  translations: Translation[];
}

export interface Translation {
  object_id: number;
  value: string;
}
