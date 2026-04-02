export interface ServicesCategoriesResponse {
  success: boolean;
  data: ServicesCategory[];
}

export interface ServicesCategory {
  id: number;
  translations: Translation[];
}

export interface Translation {
  object_id: number;
  value: string;
}
