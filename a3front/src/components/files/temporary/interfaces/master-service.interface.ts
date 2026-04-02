export interface MasterService {
  id: null;
  master_service_id: number;
  name: string;
  code: string;
  type_ifx: string;
  start_time: string;
  departure_time: string;
  date_in: string;
  date_out: string;
  amount_cost: string;
  rate_plan_code: string;
  totalPenalties: number;
  components: Component[];
}

export interface Component {
  composition_id: number;
  code: string;
  name: string;
  duration_minutes: number;
  rate_plan_code: string;
  is_programmable: number;
  country_in_iso: string;
  country_in_name: string;
  city_in_iso: string;
  city_in_name: string;
  country_out_iso: string;
  country_out_name: string;
  city_out_iso: string;
  city_out_name: string;
  start_time: string;
  departure_time: string;
  date_in: string;
  date_out: string;
  currency: string;
  amount_sale: number;
  amount_cost: number;
  amount_sale_origin: number;
  amount_cost_origin: number;
  taxes: number;
  total_services: number;
  use_voucher: number;
  use_itinerary: number;
  use_ticket: number;
  use_accounting_document: number;
  accounting_document_sent: number;
  document_skeleton: number;
  document_purchase_order: number;
  supplier: Supplier;
}

export interface Supplier {
  reservation_for_send: number;
  for_assign: number;
  code_request_book: string;
  code_request_invoice: string;
  code_request_voucher: string;
  send_communication: string;
}
