export interface SuppliersTransportResponseInterface {
  success: boolean;
  data: Data[];
  pagination: Pagination;
  code: number;
}

interface Data {
  id: number;
  user_id: number;
  code: string;
  name: string;
  ruc: string;
  dni: string;
  quantity_type_unit_transports: QuantityTypeUnitTransport[];
  status: number;
  created_at: string;
  updated_at: string;
}

interface QuantityTypeUnitTransport {
  id: number;
  name: string;
  quantity: number;
}

interface Pagination {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
}
