export interface BaseDrawerProps {
  showDrawerForm: boolean;
  supplierOriginalId: number | null;
  productSupplierId: string | null;
}

export interface DrawerEmits {
  'update:showDrawerForm': [value: boolean];
}
export interface DrawerEmitTypeInterface {
  (event: 'update:showDrawerForm', value: boolean): void;
}
