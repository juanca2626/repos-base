export interface SupplierRouteAction {
  createClassification?: string;
  editClassification?: string;
  create: string;
  edit: string;
  list: string;
}

export interface SupplierRouteActionMap {
  [key: number]: SupplierRouteAction;
}
