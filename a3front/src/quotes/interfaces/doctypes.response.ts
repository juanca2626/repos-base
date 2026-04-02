export interface DoctypesResponse {
  success: boolean;
  data: Doctype[];
}

export interface Doctype {
  id: number;
  iso: string;
  created_at: null;
  updated_at: null;
  deleted_at: null;
  translations: Translation[];
}

export interface Translation {
  id: number;
  type: string;
  object_id: number;
  slug: string;
  value: string;
  language_id: number;
  created_at: null;
  updated_at: null;
  deleted_at: null;
}
