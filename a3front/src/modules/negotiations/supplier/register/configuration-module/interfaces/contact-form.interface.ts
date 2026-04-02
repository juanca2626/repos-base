export interface ContactForm {
  id: number | null;
  supplierBranchOfficeId: string | null;
  departmentId: number | null;
  typeContactId: number | null;
  firstname: string | null;
  surname: string | null;
  email: string | null;
  phone: string | null;
  method: string;
}
