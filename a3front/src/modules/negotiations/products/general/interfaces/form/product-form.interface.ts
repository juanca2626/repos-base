export interface ProductForm {
  id: string | null;
  serviceTypeId: string | null;
  serviceTypeName: string | null;
  code: string | null;
  name: string | null;
}

export interface FieldAvailability {
  isAvailable: boolean;
  isLoading: boolean;
}

export interface FieldsAvailability {
  code: FieldAvailability;
  name: FieldAvailability;
}
