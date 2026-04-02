//supplier-clone-response.interface.ts
export interface SupplierRegistration {
  id: number;
  supplier_progress_module_id: number;
  module_name: string;
}
export interface ModuleConfiguration {
  supplier_sub_classification_id: number;
  name: string;
  modules: SupplierRegistration[];
}
