export interface SelectOption {
  label: string;
  value: string | number;
}

export interface SelectMultipleOption {
  value: string;
  name: string;
  label: string;
}

export interface DrawerEmitTypeInterface {
  (event: 'update:showDrawerForm', value: boolean): void;
}

export interface ModalEmitTypeInterface {
  (event: 'update:showModal', value: boolean): void;
}
