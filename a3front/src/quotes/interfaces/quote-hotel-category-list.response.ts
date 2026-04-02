export interface QuoteHotelCategoryListResponse {
  success: boolean;
  data: Datum[];
}

export interface Datum {
  id: number;
  code: string;
  color: string;
  checked: boolean;
  translations: Translation[];
}

export interface Translation {
  object_id: number;
  value: string;
}
