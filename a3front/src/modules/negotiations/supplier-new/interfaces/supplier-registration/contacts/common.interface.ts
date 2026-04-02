import type { SupplierContactForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export interface ContactInformationSummary {
  key: keyof Partial<SupplierContactForm>;
  label: string;
  format?: (value: any) => string;
}
