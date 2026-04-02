export interface TypeContact {
  id: number;
  name: string;
}

export interface State {
  id: number;
  name: string;
}

export interface ContactResponse {
  id: number;
  supplier_id: number;
  type_contact: TypeContact;
  state: State;
  full_name: string;
  phone: string | null;
  email: string | null;
}

export interface SupplierContactResponse {
  id: number;
  country_phone_code: string | null;
  state_phone_code: string | null;
  phone: string | null;
  email: string | null;
  web: string | null;
  contacts: ContactResponse[];
}
