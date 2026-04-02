export interface LanguagesResponse {
  success: boolean;
  data: Language[];
}

export interface Language {
  id: number;
  name: string;
  iso: string;
  created_at: Date;
  updated_at: Date | null;
  deleted_at: null;
  state: number;
}
