export interface ContactForm {
  id?: number | null;
  typeContactId: number | null;
  typeContactName: string | null;
  stateId: number | null;
  stateName: string | null;
  fullName: string | null;
  phone: string | null;
  email: string | null;
  isEditMode: boolean;
}

export interface SupplierContactForm {
  web: string | null;
  countryPhoneCode: string | null;
  statePhoneCode: string | null;
  phone: string | null;
  email: string | null;
  contacts: ContactForm[];
}
