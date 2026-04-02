import type { CommercialInformationCruiseForm } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration/commercial-information/form.interface';

export interface CommercialInformationCruiseSummary {
  key: keyof CommercialInformationCruiseForm;
  label: string;
  format?: (value: any) => string;
}
