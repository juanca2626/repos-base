export interface responseLaw {
  success: boolean;
  data: Data;
  code: number;
}

export interface Data {
  id: number;
  name: string;
  description: null;
  date_from: string;
  date_to: string;
  igv_tax: number;
  creation_date: string;
}
