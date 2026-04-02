import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface GeneralInformationRequest {
  code: string;
  business_name: string;
  company_name: string;
  ruc_number: string;
  dni_number: string | null;
  authorized_management: boolean;
  chain_id: number | null;
  status: SupplierStatusEnum;
  reason_state?: string | null;
}
