export interface Guideline {
  _id: string;
  code: string;
  description: string;
  order?: number;
  options: Option[];
  observations: boolean;
  status: boolean;
}

export interface Option {
  element: string;
  type: string;
  category: string;
  entity: boolean;
  description?: string;
  values?: Value[];
}

export interface Value {
  description: string;
  value?: number;
}
