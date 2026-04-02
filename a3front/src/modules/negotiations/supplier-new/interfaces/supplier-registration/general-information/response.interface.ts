import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface Chain {
  id: number;
  name: string;
}

export interface SupplierChain {
  id: number;
  supplier_id: number;
  chain_id: number;
}

export interface GeneralInformationResponse {
  id: number;
  code: string;
  business_name: string;
  company_name: string;
  ruc_number: string;
  dni_number: string;
  authorized_management: boolean;
  status: SupplierStatusEnum;
  supplier_chain: SupplierChain | null;
  reason_state?: string | null;
}
