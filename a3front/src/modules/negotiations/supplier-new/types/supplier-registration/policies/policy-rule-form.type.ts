import type { FormFieldError } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration';

export type FormSectionErrors = Record<string, FormFieldError>;

export type FormError = {
  [key: string]: FormSectionErrors | FormSectionErrors[];
  paymentTerm: FormSectionErrors;
  partialPayments: FormSectionErrors[];
  cancellations: FormSectionErrors[];
  reconfirmations: FormSectionErrors[];
  released: FormSectionErrors[];
  children: FormSectionErrors;
};
