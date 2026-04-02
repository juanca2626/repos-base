import type { Location } from '@/modules/negotiations/supplier/interfaces';

interface SupplierBranchOffice {
  id: number;
  subClassificationSupplierId: number;
  country: Location;
  state: Location;
  city?: Location | null;
  zone?: Location | null;
}

interface Department {
  id: number;
  name: string;
}

interface TypeContact {
  id: number;
  name: string;
}

export interface ContactResponse {
  id: number;
  firstname: string;
  surname: string;
  email: string;
  phone: string;
  supplierBranchOffice: SupplierBranchOffice;
  department: Department;
  typeContact: TypeContact;
}
