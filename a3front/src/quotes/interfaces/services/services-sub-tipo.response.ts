export interface ServicesSubTypesResponse {
  success: boolean;
  data: ServicesSubType[];
}

export interface ServicesSubType {
  id: number;
  service_category_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: null;
  order: number;
  translations: Translation[];
}

export interface Translation {
  id: number;
  type: string;
  object_id: number;
  slug: string;
  value: string;
  language_id: number;
  created_at: string;
  updated_at: string;
  deleted_at: null;
}
