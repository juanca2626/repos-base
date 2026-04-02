import type { SupplierStatusEnum } from '@/modules/negotiations/suppliers/enums/supplier-status.enum';

export interface GeneralInformationForm {
  code: string;
  businessName: string;
  companyName: string;
  rucNumber: string;
  dniNumber: string | null;
  authorizedManagement: boolean;
  chainId: number | null | undefined;
  chainName?: string | null;
  status: SupplierStatusEnum | null;
  statusName?: string | null;
  reason_state?: string | null;
}
