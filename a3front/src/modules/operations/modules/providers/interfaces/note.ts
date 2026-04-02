export interface SimplifiedNote {
  classification_name: string;
  description: string;
  user_by_code: string;
  date: string;
}

export interface SimplifiedServiceNote {
  service_id: number;
  service_name: string;
  service_entity: string;
  service_code: string;
  service_category: string;
  notes: SimplifiedNote[];
}
