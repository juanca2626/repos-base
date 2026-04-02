import type { GeneralInformationForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export interface GeneralInformationSummary {
  key: keyof GeneralInformationForm;
  label: string;
  format?: (value: any) => string;
}
