export interface Vehicle {
  id?: string | number;
  code: any;
  units: number;
  usage: any;
  minCapacity: string;
  maxCapacity: string;
  includeRepresentative: boolean;
  representativeQty: string;
  isEditing: boolean;
  productsItem?: string[];
}
